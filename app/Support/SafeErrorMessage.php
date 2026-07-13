<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Convert exceptions into short, user-safe messages (never expose SQL/DB internals).
 */
class SafeErrorMessage
{
    public static function from(
        Throwable $e,
        string $fallback = 'Something went wrong. Please check your details and try again.'
    ): string {
        if ($e instanceof ValidationException) {
            $first = collect($e->errors())->flatten()->first();
            return is_string($first) && $first !== '' ? $first : 'Please check the form and try again.';
        }

        $raw = (string) $e->getMessage();

        if ($e instanceof QueryException || self::looksLikeSqlLeak($raw)) {
            return self::fromQueryMessage($raw, $fallback);
        }

        // Allow short app-level messages we intentionally throw
        if (
            ($e instanceof \RuntimeException || $e instanceof \InvalidArgumentException)
            && $raw !== ''
            && strlen($raw) <= 220
            && !self::looksLikeSqlLeak($raw)
        ) {
            return $raw;
        }

        return $fallback;
    }

    private static function fromQueryMessage(string $raw, string $fallback): string
    {
        $lower = strtolower($raw);

        if (
            str_contains($lower, 'parents_phone_unique')
            || (str_contains($lower, 'duplicate entry') && str_contains($lower, 'parents'))
        ) {
            return 'This phone number is already registered. Please search for the existing parent and select them, or use a different number.';
        }

        if (
            str_contains($lower, 'admission_number')
            || (str_contains($lower, 'duplicate entry') && str_contains($lower, 'students'))
        ) {
            return 'This admission number is already in use. Please use a different admission number.';
        }

        if (str_contains($lower, 'users_email_unique') || (str_contains($lower, 'duplicate entry') && str_contains($lower, 'users'))) {
            return 'An account with these details already exists. Please try different details.';
        }

        if (str_contains($lower, 'duplicate entry') || str_contains($lower, 'integrity constraint')) {
            return 'This record already exists. Please check your details and try again.';
        }

        return $fallback;
    }

    private static function looksLikeSqlLeak(string $raw): bool
    {
        return (bool) preg_match(
            '/SQLSTATE|Integrity constraint|Connection:\s*mysql|Host:\s*|Port:\s*\d+|Database:\s*|SQL:\s*insert|SQL:\s*update|SQL:\s*select|stack trace|Base table or view/i',
            $raw
        );
    }
}
