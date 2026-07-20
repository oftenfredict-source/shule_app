<?php

namespace App\Http\Controllers;

use App\Services\OnlineApplicationAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnlineApplicationAuthController extends Controller
{
    private const REDIRECT_SESSION_KEY = 'online_application_intended_url';

    public function __construct(
        private OnlineApplicationAuthService $authService
    ) {
    }

    private function validateTzPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        // Must be submitted as 255XXXXXXXXX (12 digits, mobile 6/7).
        if (!preg_match('/^255[67]\d{8}$/', $digits)) {
            return null;
        }

        return $digits;
    }

    private function rememberRedirect(?string $redirect): void
    {
        $safe = $this->safeRedirect($redirect);
        if ($safe) {
            session([self::REDIRECT_SESSION_KEY => $safe]);
        }
    }

    /**
     * Allow only online-application destinations; rebuild on the current request host
     * so LAN / local / production share links all continue to the apply form after login.
     */
    private function safeRedirect(?string $redirect): ?string
    {
        if (!$redirect) {
            return null;
        }

        $redirect = trim(html_entity_decode($redirect, ENT_QUOTES));

        if (str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
            if (!$this->isAllowedOnlineApplicationPath($redirect)) {
                return null;
            }

            return $this->absoluteOnCurrentHost($redirect);
        }

        if (!filter_var($redirect, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($redirect);
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';
        $pathWithQuery = $path . $query . $fragment;

        if (!$this->isAllowedOnlineApplicationPath($pathWithQuery)) {
            return null;
        }

        return $this->absoluteOnCurrentHost($pathWithQuery);
    }

    private function isAllowedOnlineApplicationPath(string $pathOrUrl): bool
    {
        $path = parse_url($pathOrUrl, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = $pathOrUrl;
        }

        return str_starts_with($path, '/online-application');
    }

    private function absoluteOnCurrentHost(string $pathWithQuery): string
    {
        if (!str_starts_with($pathWithQuery, '/')) {
            $pathWithQuery = '/' . ltrim($pathWithQuery, '/');
        }

        return rtrim(request()->root(), '/') . $pathWithQuery;
    }

    private function consumeRedirect(?string $fallback = null): string
    {
        $fromSession = session()->pull(self::REDIRECT_SESSION_KEY);
        $safe = $this->safeRedirect($fromSession) ?: $this->safeRedirect($fallback);

        return $safe ?: route('online_application.portal.dashboard');
    }

    public function showRegister(Request $request)
    {
        if (auth('online_application')->check()) {
            return redirect()->to($this->consumeRedirect($request->query('redirect')));
        }

        $this->rememberRedirect($request->query('redirect'));
        $redirect = $this->safeRedirect($request->query('redirect'))
            ?: session(self::REDIRECT_SESSION_KEY);

        return view('online_application.auth.register', compact('redirect'));
    }

    public function register(Request $request)
    {
        $this->rememberRedirect($request->input('redirect'));

        $request->merge([
            'phone' => preg_replace('/\D+/', '', (string) $request->input('phone')),
        ]);

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^255[67]\d{8}$/'],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'phone.regex' => 'Phone must start with 255 and be a valid Tanzania mobile (e.g. 255712345678).',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput($request->except('password', 'password_confirmation'));
        }

        $phone = $this->validateTzPhone($request->input('phone'));
        if (!$phone) {
            $message = 'Phone must start with 255 and be a valid Tanzania mobile (e.g. 255712345678).';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['phone' => $message])->withInput($request->except('password', 'password_confirmation'));
        }

        if ($this->authService->phoneExists($phone)) {
            $message = 'This phone number already has an account. Please login instead.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()
                ->withErrors(['phone' => $message])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            $token = $this->authService->startRegistration([
                'phone' => $phone,
                'first_name' => $request->input('first_name'),
                'middle_name' => $request->input('middle_name'),
                'last_name' => $request->input('last_name'),
                'password' => $request->input('password'),
            ]);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()
                ->withErrors(['phone' => $e->getMessage()])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $verifyUrl = route('online_application.auth.verify_otp', ['token' => $token]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Verification code sent. Enter it to save your account.',
                'redirect' => $verifyUrl,
                'expires_in_minutes' => $this->authService->getOtpTtlMinutes(),
            ]);
        }

        return redirect()
            ->to($verifyUrl)
            ->with('success', 'Account details received. Enter the verification code we sent to your phone to save your account.');
    }

    public function checkPhone(Request $request)
    {
        $phone = $this->validateTzPhone($request->query('phone', $request->input('phone')));
        if (!$phone) {
            return response()->json([
                'success' => true,
                'exists' => false,
                'valid' => false,
            ]);
        }

        $exists = $this->authService->phoneExists($phone);

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'valid' => true,
            'message' => $exists
                ? 'This phone number already has an account. Please login instead.'
                : null,
        ]);
    }

    public function showVerifyOtp(Request $request, string $token)
    {
        $maskedPhone = $this->authService->getRegistrationPhoneMasked($token);
        if (!$maskedPhone) {
            return redirect()
                ->route('online_application.auth.register')
                ->with('error', 'Verification session expired. Please register again.');
        }

        $expiresAt = $this->authService->getRegistrationExpiresAt($token);
        $otpTtlMinutes = $this->authService->getOtpTtlMinutes();

        if ($expiresAt && time() > $expiresAt) {
            $this->authService->clearRegistrationSession();

            return redirect()
                ->route('online_application.auth.register')
                ->with('error', 'Verification code expired after ' . $otpTtlMinutes . ' minutes. Please create your account again.');
        }

        return view('online_application.auth.verify-otp', compact('token', 'maskedPhone', 'expiresAt', 'otpTtlMinutes'));
    }

    public function verifyOtp(Request $request, string $token)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|min:4|max:10',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            return back()->withErrors($validator);
        }

        try {
            $user = $this->authService->verifyRegistrationOtp($token, $request->input('otp'));
            auth('online_application')->login($user);
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['otp' => $e->getMessage()]);
        }

        $redirect = $this->consumeRedirect();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your account has been saved. You can now apply to schools online.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()
            ->to($redirect)
            ->with('success', 'Your account has been saved. You can now apply to schools online.');
    }

    public function resendOtp(Request $request, string $token)
    {
        try {
            $this->authService->resendRegistrationOtp($token);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['otp' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'A new verification code has been sent.']);
        }

        return back()->with('success', 'A new verification code has been sent to your phone.');
    }

    public function showLogin(Request $request)
    {
        if (auth('online_application')->check()) {
            return redirect()->to($this->consumeRedirect($request->query('redirect')));
        }

        $this->rememberRedirect($request->query('redirect'));
        $redirect = $this->safeRedirect($request->query('redirect'))
            ?: session(self::REDIRECT_SESSION_KEY);

        return view('online_application.auth.login', compact('redirect'));
    }

    public function login(Request $request)
    {
        $this->rememberRedirect($request->input('redirect'));

        $request->merge([
            'phone' => preg_replace('/\D+/', '', (string) $request->input('phone')),
        ]);

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^255[67]\d{8}$/'],
            'password' => 'required|string',
        ], [
            'phone.regex' => 'Phone must start with 255 and be a valid Tanzania mobile (e.g. 255712345678).',
        ]);

        $wantsJson = $request->expectsJson() || $request->ajax();

        if ($validator->fails()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput($request->only('phone', 'redirect'));
        }

        $phone = $this->validateTzPhone($request->input('phone'));
        if (!$phone) {
            $message = 'Phone must start with 255 and be a valid Tanzania mobile (e.g. 255712345678).';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()
                ->withErrors(['phone' => $message])
                ->withInput($request->only('phone', 'redirect'));
        }

        $user = $this->authService->attemptLogin(
            $phone,
            $request->input('password'),
            $request->boolean('remember')
        );

        if (!$user) {
            $message = 'Invalid phone number or password.';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()
                ->withErrors(['phone' => $message])
                ->withInput($request->only('phone', 'redirect'));
        }

        $redirect = $this->consumeRedirect($request->input('redirect'));

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()->to($redirect);
    }

    public function logout(Request $request)
    {
        auth('online_application')->logout();

        return redirect()
            ->route('online_application.auth.login')
            ->with('success', 'You have been logged out. Login again to continue applying online.');
    }
}
