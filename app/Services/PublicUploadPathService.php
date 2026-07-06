<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class PublicUploadPathService
{
    /**
     * Ordered upload directory candidates (subdomain public/, main domain public_html/, local).
     */
    public static function directoryCandidates(string $subdirectory): array
    {
        $subdirectory = trim($subdirectory, '/');
        $candidates = [];

        $scriptDir = !empty($_SERVER['SCRIPT_FILENAME'])
            ? dirname($_SERVER['SCRIPT_FILENAME'])
            : null;

        if ($scriptDir) {
            // Same public root as manage_student userImages uploads
            if (is_dir($scriptDir . '/userImages')) {
                $candidates[] = $scriptDir . '/' . $subdirectory;
            }
            $candidates[] = $scriptDir . '/' . $subdirectory;
        }

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subdirectory;
        }

        $candidates[] = public_path($subdirectory);
        $candidates[] = base_path('public/' . $subdirectory);

        $parentDir = dirname(base_path());
        if (file_exists($parentDir . '/public_html')) {
            $candidates[] = $parentDir . '/public_html/' . $subdirectory;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    public static function resolveWritableDirectory(string $subdirectory): string
    {
        $errors = [];

        foreach (self::directoryCandidates($subdirectory) as $directory) {
            self::prepareDirectory($directory);

            if (self::canWriteToDirectory($directory)) {
                return $directory;
            }

            $errors[] = $directory;
        }

        throw new RuntimeException(
            'Unable to find a writable upload directory for "' . trim($subdirectory, '/') . '". '
            . 'Checked: ' . implode(', ', $errors)
            . '. Create the folder and set permissions to 755 or 775 (same as userImages).'
        );
    }

    /**
     * @return array{absolute_path: string, relative_path: string, filename: string, directory: string}
     */
    public static function storeUploadedFile(UploadedFile $file, string $subdirectory): array
    {
        $subdirectory = trim($subdirectory, '/');
        $safeOriginal = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName()) ?: 'upload.bin';
        $fileName = time() . '_' . $safeOriginal;

        $source = $file->getRealPath();
        if (!$source || !is_readable($source)) {
            throw new RuntimeException('Uploaded file is not readable.');
        }

        $errors = [];

        foreach (self::directoryCandidates($subdirectory) as $directory) {
            self::prepareDirectory($directory);

            if (!self::canWriteToDirectory($directory)) {
                $errors[] = $directory . ' (permission denied)';
                continue;
            }

            $destination = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $fileName;

            if (@copy($source, $destination)) {
                @chmod($destination, 0644);

                return [
                    'absolute_path' => $destination,
                    'relative_path' => $subdirectory . '/' . $fileName,
                    'filename' => $fileName,
                    'directory' => $directory,
                ];
            }

            $errors[] = $directory . ' (copy failed)';
        }

        throw new RuntimeException(
            'Unable to save uploaded file to "' . $subdirectory . '". '
            . implode('; ', $errors)
        );
    }

    public static function resolveExistingFile(string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }

        $fileName = basename($relativePath);
        $subdir = trim(dirname($relativePath), '/.');
        if ($subdir === '') {
            $subdir = 'exam_papers';
        }

        $candidates = [];
        foreach (self::directoryCandidates($subdir) as $dir) {
            $candidates[] = rtrim($dir, '/') . '/' . $fileName;
        }

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $relativePath;
        }
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relativePath;
        }
        $candidates[] = public_path($relativePath);
        $candidates[] = dirname(base_path()) . '/public_html/' . $relativePath;

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

    private static function prepareDirectory(string $path): void
    {
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        $permissionTemplate = dirname($path) . '/userImages';
        if (is_dir($permissionTemplate)) {
            $perms = fileperms($permissionTemplate) & 0777;
            if ($perms > 0) {
                @chmod($path, $perms);
                return;
            }
        }

        @chmod($path, 0775);
    }

    private static function canWriteToDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $probe = $path . '/.upload_probe_' . uniqid('', true);
        $written = @file_put_contents($probe, 'ok');

        if ($written !== false) {
            @unlink($probe);

            return true;
        }

        return false;
    }
}
