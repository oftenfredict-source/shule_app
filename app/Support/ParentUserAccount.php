<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Ensure a parent has a login row without colliding on users.email / users.name.
 * Never creates/updates the parents table - only users.
 */
class ParentUserAccount
{
    public static function ensure($parent): void
    {
        if (!$parent) {
            return;
        }

        $phone = trim((string) ($parent->phone ?? ''));
        if ($phone === '') {
            return;
        }

        // Already has login under this phone username
        if (User::where('name', $phone)->exists()) {
            return;
        }

        // Try alternate phone forms (with/without 255)
        $digits = preg_replace('/\D+/', '', $phone);
        $alts = array_unique(array_filter([
            $phone,
            $digits,
            str_starts_with((string) $digits, '255') ? substr((string) $digits, 3) : null,
            (str_starts_with((string) $digits, '255') ? null : ('255' . $digits)),
        ]));
        foreach ($alts as $alt) {
            if ($alt && User::where('name', $alt)->exists()) {
                return;
            }
        }

        $email = trim((string) ($parent->email ?? ''));
        $email = $email !== '' ? $email : null;
        if ($email && User::where('email', $email)->exists()) {
            $email = null;
        }
        if (!$email) {
            $seed = $digits !== '' ? $digits : ('p' . ($parent->parentID ?? uniqid()));
            $email = $seed . '@parent.local';
            $i = 1;
            while (User::where('email', $email)->exists()) {
                $email = $seed . '_' . $i . '@parent.local';
                $i++;
            }
        }

        User::create([
            'name' => $phone,
            'email' => $email,
            'password' => Hash::make($parent->first_name ?: $phone),
            'user_type' => 'parent',
        ]);
    }
}
