<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class PublicUploadPathService
{
    /**
     * Resolve upload directory using the same strategy as manage_student:
     * 1) folder next to index.php (SCRIPT_FILENAME) — subdomain / local public/
     * 2) DOCUMENT_ROOT subfolder when document root points to public/
     * 3) Laravel public_path()
     * 4) cPanel public_html for main domain installs
     *
     * Also mirrors userImages when it already exists (proven writable on the server).
     */
    public static function resolveWritableDirectory(string $subdirectory): string
    {
        $subdirectory = trim($subdirectory, '/');

        $scriptDir = !empty($_SERVER['SCRIPT_FILENAME'])
            ? dirname($_SERVER['SCRIPT_FILENAME'])
            : public_path();

        // If student photos already upload here, use the same public root for other folders.
        $userImagesDir = $scriptDir . '/userImages';
        if (is_dir($userImagesDir)) {
            $siblingPath = $scriptDir . '/' . $subdirectory;
            self::ensureDirectory($siblingPath);

            if (is_dir($siblingPath)) {
                return $siblingPath;
            }
        }

        // manage_student primary path: directory where index.php lives (e.g. public/exam_papers)
        $uploadPath = $scriptDir . '/' . $subdirectory;

        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = !empty($_SERVER['DOCUMENT_ROOT'])
                ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subdirectory
                : null;

            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path($subdirectory);
            }
        }

        self::ensureDirectory($uploadPath);

        if (!is_dir($uploadPath)) {
            $parentDir = dirname(base_path());
            $publicHtmlPath = $parentDir . '/public_html/' . $subdirectory;

            if (file_exists($parentDir . '/public_html') && (is_dir($publicHtmlPath) || @mkdir($publicHtmlPath, 0777, true))) {
                $uploadPath = $publicHtmlPath;
            }
        }

        if (!is_dir($uploadPath)) {
            throw new RuntimeException(
                'Unable to find upload directory for "' . $subdirectory . '". '
                . 'Tried: ' . $scriptDir . '/' . $subdirectory
                . (!empty($_SERVER['DOCUMENT_ROOT']) ? ', ' . rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subdirectory : '')
                . ', ' . public_path($subdirectory)
            );
        }

        return $uploadPath;
    }

    private static function ensureDirectory(string $path): void
    {
        if (!file_exists($path)) {
            @mkdir($path, 0755, true);
        }
    }

    /**
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

    public static function directoryCandidates(string $subdirectory): array
    {
        $subdirectory = trim($subdirectory, '/');
        $candidates = [];

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $subdirectory;
        }

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subdirectory;
        }

        $candidates[] = public_path($subdirectory);
        $candidates[] = base_path('public/' . $subdirectory);

        $parentDir = dirname(base_path());
        $candidates[] = $parentDir . '/public_html/' . $subdirectory;

        return array_values(array_unique(array_filter($candidates)));
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
}
