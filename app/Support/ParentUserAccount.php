<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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

        if (self::findParentUser($phone)) {
            return;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        $email = self::uniqueParentEmail($parent, (string) $digits);

        self::createLogin(
            $phone,
            $email,
            Hash::make($parent->first_name ?: $phone),
            $parent
        );
    }

    /**
     * Force username = phone and password = first name.
     * Creates the users row if missing, or overwrites an existing parent login
     * even if the parent already changed their password. Never sends SMS.
     *
     * @return array{ok:bool,action:string,message:string}
     */
    public static function forceSync($parent): array
    {
        if (!$parent) {
            return [
                'ok' => false,
                'action' => 'skipped',
                'message' => 'Parent not found',
            ];
        }

        $phone = trim((string) ($parent->phone ?? ''));
        $firstName = trim((string) ($parent->first_name ?? ''));

        if ($phone === '') {
            return [
                'ok' => false,
                'action' => 'skipped',
                'message' => 'Phone number is missing',
            ];
        }

        if ($firstName === '') {
            return [
                'ok' => false,
                'action' => 'skipped',
                'message' => 'First name is missing',
            ];
        }

        $user = self::findParentUser($phone);
        $hashed = Hash::make($firstName);

        if ($user) {
            $other = User::where('name', $phone)
                ->where('id', '!=', $user->id)
                ->first();
            if ($other) {
                $otherType = strtolower((string) ($other->user_type ?? ''));
                if ($otherType !== 'parent') {
                    return [
                        'ok' => false,
                        'action' => 'skipped',
                        'message' => 'Phone number is already used by another account',
                    ];
                }
                self::writeLogin($other, $phone, $hashed, $parent);

                return [
                    'ok' => true,
                    'action' => 'updated',
                    'message' => 'Account updated. Username is the phone number, password is the first name.',
                ];
            }

            self::writeLogin($user, $phone, $hashed, $parent);

            return [
                'ok' => true,
                'action' => 'updated',
                'message' => 'Account updated. Username is the phone number, password is the first name.',
            ];
        }

        $taken = User::where('name', $phone)->first();
        if ($taken) {
            $takenType = strtolower((string) ($taken->user_type ?? ''));
            if ($takenType !== '' && $takenType !== 'parent') {
                return [
                    'ok' => false,
                    'action' => 'skipped',
                    'message' => 'Phone number is already used by another account',
                ];
            }
            self::writeLogin($taken, $phone, $hashed, $parent);

            return [
                'ok' => true,
                'action' => 'updated',
                'message' => 'Account updated. Username is the phone number, password is the first name.',
            ];
        }

        $digits = preg_replace('/\D+/', '', $phone);
        self::createLogin($phone, self::uniqueParentEmail($parent, (string) $digits), $hashed, $parent);

        return [
            'ok' => true,
            'action' => 'created',
            'message' => 'Account created. Username is the phone number, password is the first name.',
        ];
    }

    public static function findParentUser(string $phone): ?User
    {
        $alts = self::phoneUsernames($phone);
        if ($alts === []) {
            return null;
        }

        return User::whereIn('name', $alts)
            ->whereRaw('LOWER(COALESCE(user_type, \'\')) = ?', ['parent'])
            ->first();
    }

    /**
     * @return list<string>
     */
    public static function phoneUsernames(string $phone): array
    {
        $phone = trim($phone);
        $digits = preg_replace('/\D+/', '', $phone);
        $alts = [
            $phone,
            $digits,
            str_starts_with((string) $digits, '255') ? substr((string) $digits, 3) : null,
            (str_starts_with((string) $digits, '255') ? null : ('255' . $digits)),
        ];

        return array_values(array_unique(array_filter($alts, function ($value) {
            return $value !== null && $value !== '';
        })));
    }

    private static function uniqueParentEmail($parent, string $digits): string
    {
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

        return $email;
    }

    private static function writeLogin(User $user, string $phone, string $hashedPassword, $parent): void
    {
        $data = [
            'name' => $phone,
            'password' => $hashedPassword,
            'user_type' => 'parent',
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('users', 'schoolID') && !empty($parent->schoolID)) {
            $data['schoolID'] = (int) $parent->schoolID;
        }
        User::where('id', $user->id)->update($data);
    }

    private static function createLogin(string $phone, string $email, string $hashedPassword, $parent): void
    {
        $data = [
            'name' => $phone,
            'email' => $email,
            'password' => $hashedPassword,
            'user_type' => 'parent',
        ];
        if (Schema::hasColumn('users', 'schoolID') && !empty($parent->schoolID)) {
            $data['schoolID'] = (int) $parent->schoolID;
        }
        User::create($data);
    }
}
