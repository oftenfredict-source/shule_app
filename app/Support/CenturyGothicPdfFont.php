<?php

namespace App\Support;

use Dompdf\Dompdf;

/**
 * Registers Century Gothic for DomPDF (CSS family name alone is not enough).
 */
class CenturyGothicPdfFont
{
    public const FAMILY = 'century gothic';

    public static function fontFilePath(): string
    {
        return storage_path('fonts/century-gothic.ttf');
    }

    /**
     * Copy system font into storage/fonts if missing, then register with DomPDF.
     */
    public static function ensureRegistered(?Dompdf $dompdf = null): bool
    {
        $target = self::fontFilePath();
        $fontDir = dirname($target);

        if (!is_dir($fontDir)) {
            @mkdir($fontDir, 0755, true);
        }

        if (!is_readable($target)) {
            foreach (self::sourceCandidates() as $source) {
                if ($source !== $target && is_readable($source)) {
                    @copy($source, $target);
                    break;
                }
            }
        }

        if (!is_readable($target)) {
            return false;
        }

        if ($dompdf !== null) {
            $metrics = $dompdf->getFontMetrics();
            $metrics->registerFont(
                [
                    'family' => self::FAMILY,
                    'style' => 'normal',
                    'weight' => 'normal',
                ],
                $target,
            );
            $dompdf->getOptions()->setDefaultFont(self::FAMILY);
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private static function sourceCandidates(): array
    {
        return array_filter([
            self::fontFilePath(),
            public_path('fonts/century-gothic.ttf'),
            public_path('fonts/GOTHIC.TTF'),
            'C:/Windows/Fonts/GOTHIC.TTF',
            'C:\\Windows\\Fonts\\GOTHIC.TTF',
            '/usr/share/fonts/truetype/msttcorefonts/GOTHIC.TTF',
            '/usr/share/fonts/truetype/microsoft/GOTHIC.TTF',
        ]);
    }
}
