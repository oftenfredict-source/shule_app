<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PublicUploadPathService
{
    /**
     * Public web paths (same order as manage_student).
     */
    public static function publicDirectoryCandidates(string $subdirectory): array
    {
        $subdirectory = trim($subdirectory, '/');
        $candidates = [];

        $scriptDir = !empty($_SERVER['SCRIPT_FILENAME'])
            ? dirname($_SERVER['SCRIPT_FILENAME'])
            : null;

        if ($scriptDir) {
            $candidates[] = $scriptDir . '/' . $subdirectory;
        }

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $subdirectory;
        }

        $candidates[] = public_path($subdirectory);

        $parentDir = dirname(base_path());
        if (file_exists($parentDir . '/public_html')) {
            $candidates[] = $parentDir . '/public_html/' . $subdirectory;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    public static function directoryCandidates(string $subdirectory): array
    {
        $dirs = self::publicDirectoryCandidates($subdirectory);
        $dirs[] = storage_path('app/public/' . trim($subdirectory, '/'));

        return array_values(array_unique(array_filter($dirs)));
    }

    /**
     * @return array{absolute_path: string, relative_path: string, filename: string, directory: string, disk: string}
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

        $publicErrors = [];

        // 1) Public folder — identical strategy to manage_student (public/exam_papers on subdomain)
        foreach (self::publicDirectoryCandidates($subdirectory) as $directory) {
            $directory = self::preparePublicDirectory($directory, $subdirectory);

            $destination = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            if (@copy($source, $destination)) {
                @chmod($destination, 0644);

                return self::buildResult($subdirectory, $fileName, $directory, 'public');
            }

            $publicErrors[] = $directory;
        }

        // 2) Laravel storage/app/public — writable even when public/exam_papers is not
        try {
            $relativePath = trim($subdirectory, '/') . '/' . $fileName;
            $storageRoot = storage_path('app/public/' . $subdirectory);
            if (!is_dir($storageRoot)) {
                @mkdir($storageRoot, 0755, true);
            }

            if (@copy($source, $storageRoot . DIRECTORY_SEPARATOR . $fileName)) {
                return self::buildResult($subdirectory, $fileName, $storageRoot, 'storage');
            }

            $stored = $file->storeAs($subdirectory, $fileName, 'public');
            if ($stored) {
                return self::buildResult(
                    $subdirectory,
                    $fileName,
                    Storage::disk('public')->path(dirname($stored)),
                    'storage'
                );
            }
        } catch (\Throwable $e) {
            $publicErrors[] = 'storage: ' . $e->getMessage();
        }

        throw new RuntimeException(
            'Unable to save uploaded file to "' . $subdirectory . '". '
            . 'Public paths failed (' . implode(', ', $publicErrors) . '). '
            . 'Please ensure public/' . $subdirectory . ' is writable (chmod 775, same as userImages) '
            . 'or storage/app/public is writable.'
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

        foreach (self::publicDirectoryCandidates($subdir) as $dir) {
            $candidates[] = rtrim($dir, '/') . '/' . $fileName;
        }

        $candidates[] = storage_path('app/public/' . $relativePath);

        if (Storage::disk('public')->exists($relativePath)) {
            $candidates[] = Storage::disk('public')->path($relativePath);
        }

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

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    public static function resolveWritableDirectory(string $subdirectory): string
    {
        foreach (self::directoryCandidates($subdirectory) as $directory) {
            if (str_contains($directory, storage_path('app'))) {
                if (!is_dir($directory)) {
                    @mkdir($directory, 0755, true);
                }
                if (is_dir($directory)) {
                    return $directory;
                }
                continue;
            }

            $directory = self::preparePublicDirectory($directory, $subdirectory);
            if (is_dir($directory)) {
                return $directory;
            }
        }

        throw new RuntimeException('Unable to resolve upload directory for "' . trim($subdirectory, '/') . '".');
    }

    private static function preparePublicDirectory(string $path, string $subdirectory): string
    {
        $scriptDir = !empty($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : null;
        $userImagesDir = $scriptDir ? $scriptDir . '/userImages' : null;

        // manage_student flow
        if (!is_dir($path) && !@mkdir($path, 0777, true)) {
            $docRootPath = !empty($_SERVER['DOCUMENT_ROOT'])
                ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . trim($subdirectory, '/')
                : null;

            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $path = $docRootPath;
            } else {
                $path = public_path(trim($subdirectory, '/'));
            }
        }

        if (!file_exists($path)) {
            @mkdir($path, 0755, true);
        }

        if ($userImagesDir && is_dir($userImagesDir)) {
            $perms = fileperms($userImagesDir) & 0777;
            if ($perms > 0) {
                @chmod($path, $perms);
            }
        } else {
            @chmod($path, 0775);
        }

        return $path;
    }

  /**
     * @return array{absolute_path: string, relative_path: string, filename: string, directory: string, disk: string}
     */
    private static function buildResult(string $subdirectory, string $fileName, string $directory, string $disk): array
    {
        return [
            'absolute_path' => rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $fileName,
            'relative_path' => trim($subdirectory, '/') . '/' . $fileName,
            'filename' => $fileName,
            'directory' => $directory,
            'disk' => $disk,
        ];
    }
}
