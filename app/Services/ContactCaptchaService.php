<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ContactCaptchaService
{
    public const SESSION_KEY = 'contact_captcha';

    public function regenerate(): string
    {
        $code = $this->makeCode(5);
        Session::put(self::SESSION_KEY, [
            'hash' => hash('sha256', Str::lower($code) . config('app.key')),
            'expires_at' => time() + 600,
            'attempts' => 0,
        ]);

        return $code;
    }

    public function currentCodeForImage(): string
    {
        $payload = Session::get(self::SESSION_KEY);
        if (!$payload || empty($payload['expires_at']) || time() > (int) $payload['expires_at']) {
            return $this->regenerate();
        }

        // Image refresh should rotate the challenge so bots cannot reuse a leaked code.
        return $this->regenerate();
    }

    public function validate(?string $answer): bool
    {
        $payload = Session::get(self::SESSION_KEY);
        if (!$payload || empty($payload['hash']) || empty($payload['expires_at'])) {
            return false;
        }

        if (time() > (int) $payload['expires_at']) {
            Session::forget(self::SESSION_KEY);
            return false;
        }

        $attempts = (int) ($payload['attempts'] ?? 0);
        if ($attempts >= 8) {
            Session::forget(self::SESSION_KEY);
            return false;
        }

        $normalized = Str::lower(preg_replace('/\s+/', '', (string) $answer) ?? '');
        $expected = hash('sha256', $normalized . config('app.key'));
        $ok = hash_equals((string) $payload['hash'], $expected);

        if (!$ok) {
            $payload['attempts'] = $attempts + 1;
            Session::put(self::SESSION_KEY, $payload);
            return false;
        }

        Session::forget(self::SESSION_KEY);
        return true;
    }

    public function renderPng(string $code): string
    {
        $width = 220;
        $height = 72;
        $image = imagecreatetruecolor($width, $height);

        $bgTop = imagecolorallocate($image, 255, 248, 248);
        $bgBottom = imagecolorallocate($image, 255, 236, 236);
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) (255 + (255 - 255) * $ratio);
            $g = (int) (248 + (236 - 248) * $ratio);
            $b = (int) (248 + (236 - 248) * $ratio);
            $rowColor = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $rowColor);
        }

        // Soft noise dots
        for ($i = 0; $i < 120; $i++) {
            $dot = imagecolorallocatealpha(
                $image,
                random_int(148, 200),
                random_int(0, 80),
                random_int(0, 80),
                random_int(70, 110)
            );
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $dot);
        }

        // Wave lines
        for ($i = 0; $i < 4; $i++) {
            $line = imagecolorallocatealpha($image, 148, 0, 0, random_int(80, 105));
            $y1 = random_int(8, $height - 8);
            $y2 = random_int(8, $height - 8);
            imageline($image, 0, $y1, $width, $y2, $line);
        }

        $chars = str_split($code);
        $count = count($chars);
        $slot = (int) floor(($width - 24) / max(1, $count));

        foreach ($chars as $index => $char) {
            $color = imagecolorallocate(
                $image,
                random_int(120, 160),
                random_int(0, 30),
                random_int(0, 30)
            );
            $size = 5;
            $x = 16 + ($index * $slot) + random_int(0, 3);
            $y = random_int(18, 28);
            $shadow = imagecolorallocatealpha($image, 80, 0, 0, 100);
            imagestring($image, $size, $x + 1, $y + 1, $char, $shadow);
            imagestring($image, $size, $x, $y, $char, $color);
        }

        // Border
        $border = imagecolorallocate($image, 228, 188, 188);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $border);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return $png ?: '';
    }

    private function makeCode(int $length): string
    {
        // Avoid ambiguous characters (0/O, 1/I/L).
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $out = '';
        $max = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }

        return $out;
    }
}
