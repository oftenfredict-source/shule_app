<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class PublicUploadPathService
{
    /**
     * Candidate absolute directories for public uploads (main domain, subdomain, local).
     */
    public static function directoryCandidates(string $subdirectory): array
    {
        $subdirectory = trim($subdirectory, '/');
        $candidates = [];

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $subdirectory;
        }

        $basePath = base_path();
        $parentDir = dirname($basePath);

        if (file_exists($parentDir . '/public_html')) {
            $candidates[] = $parentDir . '/public_html/' . $subdirectory;
        }

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subdirectory;
        }

        $candidates[] = public_path($subdirectory);
        $candidates[] = base_path('public/' . $subdirectory);
        $candidates[] = $parentDir . '/public_html/' . $subdirectory;

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * Resolve the first writable upload directory for a subdirectory.
     */
    public static function resolveWritableDirectory(string $subdirectory): string
    {
        foreach (self::directoryCandidates($subdirectory) as $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }

            if (is_dir($path) && is_writable($path)) {
                return $path;
            }
        }

        throw new RuntimeException(
            'Unable to find a writable upload directory for "' . trim($subdirectory, '/') . '".'
        );
    }

    /**
     * Store an uploaded file and return storage metadata.
     *
     * @return array{absolute_path: string, relative_path: string, filename: string, directory: string}
     */
    public static function storeUploadedFile(UploadedFile $file, string $subdirectory): array
    {
        $subdirectory = trim($subdirectory, '/');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $directory = self::resolveWritableDirectory($subdirectory);

        $file->move($directory, $fileName);

        return [
            'absolute_path' => $directory . DIRECTORY_SEPARATOR . $fileName,
            'relative_path' => $subdirectory . '/' . $fileName,
            'filename' => $fileName,
            'directory' => $directory,
        ];
    }

    /**
     * Find an existing file using the same multi-environment lookup order.
     */
    public static function resolveExistingFile(string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }

        $candidates = [];

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $relativePath;
        }

        $basePath = base_path();
        $parentDir = dirname($basePath);

        $candidates[] = public_path($relativePath);

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relativePath;
        }

        $candidates[] = $parentDir . '/public_html/' . $relativePath;
        $candidates[] = base_path('public/' . $relativePath);

        foreach (array_unique(array_filter($candidates)) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function deleteIfExists(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $absolute = self::resolveExistingFile($relativePath);
        if ($absolute && file_exists($absolute)) {
            @unlink($absolute);
        }
    }
}
