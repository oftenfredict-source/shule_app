<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class OnlineApplicationApplyTokenService
{
    private const TTL_HOURS = 48;

    /** Legacy Crypt-based school tokens expired after this many days. */
    private const SCHOOL_TTL_DAYS = 365;

    public function encode(int $schoolID, int $classID, ?int $academicYearID = null): string
    {
        $payload = json_encode([
            'type' => 'apply',
            'school' => $schoolID,
            'class' => $classID,
            'year' => $academicYearID,
            'exp' => now()->addHours(self::TTL_HOURS)->timestamp,
        ]);

        return $this->toUrlSafe(Crypt::encryptString($payload));
    }

    /**
     * @return array{schoolID:int, classID:int, academicYearID:?int}
     */
    public function decode(string $token): array
    {
        $data = $this->decryptPayload($token);

        $schoolID = (int) ($data['school'] ?? 0);
        $classID = (int) ($data['class'] ?? 0);
        $academicYearID = isset($data['year']) ? (int) $data['year'] : null;
        $exp = (int) ($data['exp'] ?? 0);

        if ($schoolID <= 0 || $classID <= 0) {
            throw new \InvalidArgumentException('Invalid application link.');
        }

        if ($exp > 0 && now()->timestamp > $exp) {
            throw new \InvalidArgumentException('This application link has expired. Please start again from the school list.');
        }

        return [
            'schoolID' => $schoolID,
            'classID' => $classID,
            'academicYearID' => $academicYearID ?: null,
        ];
    }

    public function applyUrl(int $schoolID, int $classID, ?int $academicYearID = null): string
    {
        return route('online_application.apply', [
            'token' => $this->encode($schoolID, $classID, $academicYearID),
        ]);
    }

    /**
     * Stable, deterministic school landing token (safe for sitemap + canonical).
     * Format: {schoolID}.{hmac32} - does not rotate on each request.
     */
    public function encodeSchool(int $schoolID): string
    {
        if ($schoolID <= 0) {
            throw new \InvalidArgumentException('Invalid school link.');
        }

        return $schoolID . '.' . $this->schoolSignature($schoolID);
    }

    public function isStableSchoolToken(string $token): bool
    {
        return (bool) preg_match('/^\d+\.[a-f0-9]{32}$/i', $token);
    }

    public function decodeSchool(string $token): int
    {
        if ($this->isStableSchoolToken($token)) {
            [$schoolIDRaw, $sig] = explode('.', $token, 2);
            $schoolID = (int) $schoolIDRaw;
            if ($schoolID <= 0 || !hash_equals($this->schoolSignature($schoolID), strtolower($sig))) {
                throw new \InvalidArgumentException('Invalid school link.');
            }

            return $schoolID;
        }

        // Legacy Laravel Crypt tokens (random IV each encode - bad for SEO).
        $data = $this->decryptPayload($token);

        $schoolID = (int) ($data['school'] ?? 0);
        $exp = (int) ($data['exp'] ?? 0);
        $type = (string) ($data['type'] ?? 'school');

        if ($schoolID <= 0) {
            throw new \InvalidArgumentException('Invalid school link.');
        }

        // Reject class-apply tokens on the school landing route.
        if ($type === 'apply' || !empty($data['class'])) {
            throw new \InvalidArgumentException('Invalid school link.');
        }

        if ($exp > 0 && now()->timestamp > $exp) {
            throw new \InvalidArgumentException('This school application link has expired. Please request a new link.');
        }

        return $schoolID;
    }

    public function schoolUrl(int $schoolID): string
    {
        return $this->publicAbsoluteUrl(route('online_application.school', [
            'token' => $this->encodeSchool($schoolID),
        ], false));
    }

    /**
     * In-app school landing URL (same host as the request - local/LAN/prod).
     */
    public function localSchoolUrl(int $schoolID): string
    {
        return route('online_application.school', [
            'token' => $this->encodeSchool($schoolID),
        ], false);
    }

    /**
     * Build an absolute URL on the public production domain (https://shulexpert.com).
     */
    public function publicAbsoluteUrl(string $pathOrUrl): string
    {
        $base = rtrim((string) config('shulexpert.public_url', 'https://shulexpert.com'), '/');

        if (preg_match('#^https?://#i', $pathOrUrl)) {
            $path = parse_url($pathOrUrl, PHP_URL_PATH) ?: '/';
            $query = parse_url($pathOrUrl, PHP_URL_QUERY);
            $fragment = parse_url($pathOrUrl, PHP_URL_FRAGMENT);
            $url = $base . $path;
            if ($query) {
                $url .= '?' . $query;
            }
            if ($fragment) {
                $url .= '#' . $fragment;
            }

            return $url;
        }

        return $base . '/' . ltrim($pathOrUrl, '/');
    }

    private function schoolSignature(int $schoolID): string
    {
        return substr(hash_hmac('sha256', 'oa-school-v1:' . $schoolID, $this->signingKey()), 0, 32);
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        return $key !== '' ? $key : 'shulexpert-oa-school';
    }

    /**
     * @return array<string, mixed>
     */
    private function decryptPayload(string $token): array
    {
        try {
            $json = Crypt::decryptString($this->fromUrlSafe($token));
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $e) {
            throw new \InvalidArgumentException('Invalid or expired link.');
        }
    }

    private function toUrlSafe(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function fromUrlSafe(string $value): string
    {
        $b64 = strtr($value, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad > 0) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        $decoded = base64_decode($b64, true);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid link.');
        }

        return $decoded;
    }
}
