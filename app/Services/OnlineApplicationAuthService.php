<?php

namespace App\Services;

use App\Models\OnlineApplicationUser;
use App\Support\ParentPhone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OnlineApplicationAuthService
{
    private const OTP_TTL_MINUTES = 10;
    private const SESSION_PREFIX = 'oa_register_';

    public function __construct(
        private SmsService $sms,
        private OnlineApplicationApplyTokenService $applyTokens
    ) {
    }

    public function phoneExists(string $phone): bool
    {
        $normalized = ParentPhone::normalize($phone) ?: preg_replace('/\D+/', '', $phone);
        if (!$normalized) {
            return false;
        }

        return OnlineApplicationUser::where('phone', $normalized)->exists()
            || OnlineApplicationUser::where('phone', $phone)->exists();
    }

    /**
     * @param  array{phone:string,first_name:string,middle_name?:string,last_name:string,password:string}  $data
     */
    public function startRegistration(array $data): string
    {
        $phone = ParentPhone::normalize($data['phone']);
        if (!$phone) {
            throw new \InvalidArgumentException('Please enter a valid Tanzania phone number.');
        }

        if ($this->phoneExists($phone)) {
            throw new \InvalidArgumentException('This phone number already has an online application account. Please login instead.');
        }

        $otp = (string) random_int(100000, 999999);
        $token = Str::random(40);

        Session::put(self::SESSION_PREFIX . 'pending', true);
        Session::put(self::SESSION_PREFIX . 'token', $token);
        Session::put(self::SESSION_PREFIX . 'code_hash', Hash::make($otp));
        Session::put(self::SESSION_PREFIX . 'expires_at', time() + (self::OTP_TTL_MINUTES * 60));
        Session::put(self::SESSION_PREFIX . 'payload', [
            'phone' => $phone,
            'first_name' => trim($data['first_name']),
            'middle_name' => $data['middle_name'] ? trim($data['middle_name']) : null,
            'last_name' => trim($data['last_name']),
            'password' => $data['password'],
        ]);

        $message = "ShuleXpert Online Admission verification code: {$otp}. Valid for " . self::OTP_TTL_MINUTES . " minutes.";
        $result = $this->sms->sendSms($phone, $message);

        if (!$result['success']) {
            $this->clearRegistrationSession();
            throw new \RuntimeException('Unable to send verification SMS. Please try again.');
        }

        return $token;
    }

    public function verifyRegistrationOtp(string $token, string $otp): OnlineApplicationUser
    {
        if (!Session::get(self::SESSION_PREFIX . 'pending')) {
            throw new \InvalidArgumentException('Verification session expired. Please register again.');
        }

        if ((string) Session::get(self::SESSION_PREFIX . 'token') !== $token) {
            throw new \InvalidArgumentException('Invalid verification request.');
        }

        if (time() > (int) Session::get(self::SESSION_PREFIX . 'expires_at', 0)) {
            $this->clearRegistrationSession();
            throw new \InvalidArgumentException('Verification code expired after ' . self::OTP_TTL_MINUTES . ' minutes. Please create your account again.');
        }

        if (!Hash::check($otp, (string) Session::get(self::SESSION_PREFIX . 'code_hash'))) {
            throw new \InvalidArgumentException('Invalid verification code.');
        }

        $payload = Session::get(self::SESSION_PREFIX . 'payload');
        if (!is_array($payload) || empty($payload['phone'])) {
            $this->clearRegistrationSession();
            throw new \InvalidArgumentException('Registration data missing. Please start again.');
        }

        if ($this->phoneExists($payload['phone'])) {
            $this->clearRegistrationSession();
            throw new \InvalidArgumentException('This phone number is already registered. Please login.');
        }

        $user = OnlineApplicationUser::create([
            'phone' => $payload['phone'],
            'first_name' => $payload['first_name'],
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'],
            'password' => $payload['password'],
            'status' => 'Active',
        ]);

        $this->clearRegistrationSession();
        $this->sendWelcomeSms($user, $payload['password']);

        return $user;
    }

    public function resendRegistrationOtp(string $token): void
    {
        if (!Session::get(self::SESSION_PREFIX . 'pending')) {
            throw new \InvalidArgumentException('Verification session expired. Please register again.');
        }

        if ((string) Session::get(self::SESSION_PREFIX . 'token') !== $token) {
            throw new \InvalidArgumentException('Invalid verification request.');
        }

        $payload = Session::get(self::SESSION_PREFIX . 'payload');
        if (!is_array($payload) || empty($payload['phone'])) {
            throw new \InvalidArgumentException('Registration data missing. Please start again.');
        }

        $otp = (string) random_int(100000, 999999);
        Session::put(self::SESSION_PREFIX . 'code_hash', Hash::make($otp));
        Session::put(self::SESSION_PREFIX . 'expires_at', time() + (self::OTP_TTL_MINUTES * 60));

        $message = "ShuleXpert Online Admission verification code: {$otp}. Valid for " . self::OTP_TTL_MINUTES . " minutes.";
        $result = $this->sms->sendSms($payload['phone'], $message);

        if (!$result['success']) {
            throw new \RuntimeException('Unable to resend verification SMS. Please try again.');
        }
    }

    public function getOtpTtlMinutes(): int
    {
        return self::OTP_TTL_MINUTES;
    }

    public function getRegistrationExpiresAt(?string $token): ?int
    {
        if ((string) Session::get(self::SESSION_PREFIX . 'token') !== (string) $token) {
            return null;
        }

        if (!Session::get(self::SESSION_PREFIX . 'pending')) {
            return null;
        }

        $expires = (int) Session::get(self::SESSION_PREFIX . 'expires_at', 0);

        return $expires > 0 ? $expires : null;
    }

    public function getRegistrationPhoneMasked(?string $token): ?string
    {
        if ((string) Session::get(self::SESSION_PREFIX . 'token') !== (string) $token) {
            return null;
        }

        $phone = Session::get(self::SESSION_PREFIX . 'payload.phone');
        if (!$phone || strlen($phone) < 7) {
            return null;
        }

        return substr($phone, 0, 6) . '***' . substr($phone, -2);
    }

    public function attemptLogin(string $phone, string $password, bool $remember = false): ?OnlineApplicationUser
    {
        $normalized = ParentPhone::normalize($phone);
        if (!$normalized) {
            return null;
        }

        $user = OnlineApplicationUser::where('phone', $normalized)->first();
        if (!$user || !$user->isActive()) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        auth('online_application')->login($user, $remember);

        return $user;
    }

    public function changePassword(OnlineApplicationUser $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \InvalidArgumentException('Current password is incorrect.');
        }

        $user->update(['password' => $newPassword]);
    }

    public function clearRegistrationSession(): void
    {
        Session::forget([
            self::SESSION_PREFIX . 'pending',
            self::SESSION_PREFIX . 'token',
            self::SESSION_PREFIX . 'code_hash',
            self::SESSION_PREFIX . 'expires_at',
            self::SESSION_PREFIX . 'payload',
        ]);
    }

    private function sendWelcomeSms(OnlineApplicationUser $user, string $plainPassword): void
    {
        $displayPhone = $user->phone;
        if (str_starts_with($displayPhone, '255') && strlen($displayPhone) >= 12) {
            $displayPhone = '0' . substr($displayPhone, 3);
        }

        $loginUrl = $this->applyTokens->publicAbsoluteUrl('/online-application/account/login');

        $message = 'Welcome to ShuleXpert Online Admission. Your account is ready. '
            . 'Username: ' . $displayPhone . '. Password: ' . $plainPassword . '. '
            . 'Please login to apply to schools: ' . $loginUrl;

        try {
            $this->sms->sendSms($user->phone, $message);
        } catch (\Throwable $e) {
            \Log::warning('Online application welcome SMS failed: ' . $e->getMessage());
        }
    }
}
