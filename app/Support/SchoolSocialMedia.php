<?php

namespace App\Support;

class SchoolSocialMedia
{
    /**
     * Supported platforms with Boxicons + brand color + input type.
     */
    public static function platforms(): array
    {
        return [
            'instagram' => [
                'label' => 'Instagram',
                'icon' => 'bxl-instagram',
                'color' => '#E4405F',
                'type' => 'url',
                'placeholder' => 'https://instagram.com/...',
            ],
            'threads' => [
                'label' => 'Threads',
                'icon' => 'bx-at',
                'color' => '#000000',
                'type' => 'url',
                'placeholder' => 'https://www.threads.com/@...',
            ],
            'facebook' => [
                'label' => 'Facebook',
                'icon' => 'bxl-facebook-circle',
                'color' => '#1877F2',
                'type' => 'url',
                'placeholder' => 'https://facebook.com/...',
            ],
            'whatsapp' => [
                'label' => 'WhatsApp',
                'icon' => 'bxl-whatsapp',
                'color' => '#25D366',
                'type' => 'phone',
                'placeholder' => '+255 7XX XXX XXX',
            ],
            'youtube' => [
                'label' => 'YouTube',
                'icon' => 'bxl-youtube',
                'color' => '#FF0000',
                'type' => 'url',
                'placeholder' => 'https://youtube.com/...',
            ],
            'twitter' => [
                'label' => 'X (Twitter)',
                'icon' => 'bxl-twitter',
                'color' => '#000000',
                'type' => 'url',
                'placeholder' => 'https://x.com/...',
            ],
            'tiktok' => [
                'label' => 'TikTok',
                'icon' => 'bxl-tiktok',
                'color' => '#010101',
                'type' => 'url',
                'placeholder' => 'https://tiktok.com/@...',
            ],
            'linkedin' => [
                'label' => 'LinkedIn',
                'icon' => 'bxl-linkedin-square',
                'color' => '#0A66C2',
                'type' => 'url',
                'placeholder' => 'https://linkedin.com/in/...',
            ],
            'telegram' => [
                'label' => 'Telegram',
                'icon' => 'bxl-telegram',
                'color' => '#26A5E4',
                'type' => 'url',
                'placeholder' => 'https://t.me/...',
            ],
        ];
    }

    /**
     * Platforms list for JS UI (indexed array with key field).
     *
     * @return list<array{key:string,label:string,icon:string,color:string,type:string,placeholder:string}>
     */
    public static function platformsForJs(): array
    {
        $out = [];
        foreach (self::platforms() as $key => $p) {
            $out[] = [
                'key' => $key,
                'label' => $p['label'],
                'icon' => $p['icon'],
                'color' => $p['color'],
                'type' => $p['type'],
                'placeholder' => $p['placeholder'],
            ];
        }

        return $out;
    }

    /**
     * Normalize request payload into clean list.
     *
     * @param  mixed  $rows
     * @return list<array{platform:string,value:string}>
     */
    public static function normalize($rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        $allowed = array_keys(self::platforms());
        $out = [];
        $seen = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $platform = strtolower(trim((string) ($row['platform'] ?? '')));
            $value = trim((string) ($row['value'] ?? ''));
            if ($platform === '' || $value === '' || !in_array($platform, $allowed, true)) {
                continue;
            }
            if (isset($seen[$platform])) {
                continue;
            }
            $seen[$platform] = true;

            if ((self::platforms())[$platform]['type'] === 'phone') {
                $value = preg_replace('/[^\d+]/', '', $value) ?: $value;
            } elseif (!preg_match('#^https?://#i', $value)) {
                $value = 'https://' . ltrim($value, '/');
            }

            $out[] = [
                'platform' => $platform,
                'value' => $value,
            ];
        }

        return array_values($out);
    }

    public static function href(string $platform, string $value): string
    {
        $meta = self::platforms()[$platform] ?? null;
        if (!$meta) {
            return $value;
        }

        if (($meta['type'] ?? '') === 'phone') {
            $digits = preg_replace('/\D+/', '', $value);
            if ($digits === '') {
                return '#';
            }
            // Tanzania local numbers starting with 0 → 255
            if (str_starts_with($digits, '0') && strlen($digits) >= 9) {
                $digits = '255' . substr($digits, 1);
            }

            return 'https://wa.me/' . $digits;
        }

        return $value;
    }
}
