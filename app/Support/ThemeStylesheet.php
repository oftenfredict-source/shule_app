<?php

namespace App\Support;

class ThemeStylesheet
{
    private const RELATIVE = 'css/shulexpert-theme.css';

    public static function linkTag(): string
    {
        $source = public_path(self::RELATIVE);
        if (!is_file($source)) {
            return '';
        }

        self::publish($source);

        $webFile = self::browserFile();
        if ($webFile !== null) {
            $href = asset(self::RELATIVE).'?v='.filemtime($webFile);

            return '<link rel="stylesheet" href="'.e($href).'">';
        }

        $css = @file_get_contents($source);
        if (!is_string($css) || $css === '') {
            return '';
        }

        return '<style id="shulexpert-theme">'.$css.'</style>';
    }

    private static function publish(string $source): void
    {
        $mtime = filemtime($source) ?: 0;

        foreach (self::targets() as $target) {
            if (self::sameFile($source, $target)) {
                continue;
            }
            if (is_file($target) && filemtime($target) >= $mtime) {
                continue;
            }

            $dir = dirname($target);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!is_dir($dir)) {
                continue;
            }
            $writable = is_file($target) ? is_writable($target) : is_writable($dir);
            if ($writable) {
                @copy($source, $target);
            }
        }
    }

    private static function browserFile(): ?string
    {
        $docRoot = self::docRoot();
        if ($docRoot === '') {
            $local = public_path(self::RELATIVE);

            return is_file($local) ? $local : null;
        }

        $web = $docRoot.'/'.self::RELATIVE;

        return is_file($web) ? $web : null;
    }

    /**
     * @return list<string>
     */
    private static function targets(): array
    {
        $targets = [];
        $docRoot = self::docRoot();
        if ($docRoot !== '') {
            $targets[] = $docRoot.'/'.self::RELATIVE;
        }
        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/');
            $targets[] = $scriptDir.'/'.self::RELATIVE;
        }
        $targets[] = rtrim(str_replace('\\', '/', dirname(base_path())), '/').'/public_html/'.self::RELATIVE;

        return array_values(array_unique($targets));
    }

    private static function docRoot(): string
    {
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            return '';
        }

        return rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    }

    private static function sameFile(string $a, string $b): bool
    {
        $left = realpath($a);
        $right = is_file($b) ? realpath($b) : false;

        return $left && $right && $left === $right;
    }
}
