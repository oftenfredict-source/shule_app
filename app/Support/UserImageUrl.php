<?php

namespace App\Support;

/**
 * Resolve userImages URLs only when the file exists; otherwise return a placeholder.
 * Avoids browser 404 spam for missing photos.
 */
class UserImageUrl
{
    /** @var array<string, string> */
    private static array $cache = [];

    public static function forGender(?string $filename, ?string $gender = null): string
    {
        $fallback = strcasecmp((string) $gender, 'Female') === 0
            ? asset('images/female.png')
            : asset('images/male.png');

        return self::asset($filename, $fallback);
    }

    public static function asset(?string $filename, ?string $fallback = null): string
    {
        $fallback = $fallback ?: asset('images/male.png');
        $filename = self::normalizeFilename($filename);
        if ($filename === null) {
            return $fallback;
        }

        if (array_key_exists($filename, self::$cache)) {
            return self::$cache[$filename] !== '' ? self::$cache[$filename] : $fallback;
        }

        foreach (self::candidatePaths($filename) as $path) {
            if (@is_file($path)) {
                return self::$cache[$filename] = asset('userImages/' . $filename);
            }
        }

        self::$cache[$filename] = '';
        return $fallback;
    }

    private static function normalizeFilename(?string $filename): ?string
    {
        if ($filename === null) {
            return null;
        }

        $filename = trim(str_replace('\\', '/', (string) $filename));
        if ($filename === '') {
            return null;
        }

        // Strip accidental URL/path prefixes stored in DB
        if (preg_match('#userImages/(.+)$#i', $filename, $m)) {
            $filename = $m[1];
        }

        $filename = basename($filename);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        return $filename;
    }

    /**
     * @return list<string>
     */
    private static function candidatePaths(string $filename): array
    {
        $paths = [
            public_path('userImages/' . $filename),
            base_path('public/userImages/' . $filename),
            dirname(base_path()) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'userImages' . DIRECTORY_SEPARATOR . $filename,
        ];

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $paths[] = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\')
                . DIRECTORY_SEPARATOR . 'userImages' . DIRECTORY_SEPARATOR . $filename;
        }

        return array_values(array_unique($paths));
    }
}
