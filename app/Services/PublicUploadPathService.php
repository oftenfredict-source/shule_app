<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Public upload paths — mirrors ManageStudentController photo upload exactly.
 */
class PublicUploadPathService
{
    /**
     * Same logic as manage_student (userImages), with a different subfolder name.
     */
    public static function resolvePublicUploadDirectory(string $folder): string
    {
        $folder = trim($folder, '/');

        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/' . $folder;

        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $folder : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path($folder);
            }
        }

        if (!file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        // userImages uploads work on this server — align exam_papers with it
        self::syncPermissionsWithUserImages($uploadPath, $scriptDir . '/userImages');

        return $uploadPath;
    }

    /**
     * @return array{absolute_path: string, relative_path: string, filename: string, directory: string}
     */
    public static function storeUploadedFile(UploadedFile $file, string $folder): array
    {
        $folder = trim($folder, '/');
        $uploadPath = self::resolvePublicUploadDirectory($folder);
        $fileName = time() . '_' . $file->getClientOriginalName();

        $file->move($uploadPath, $fileName);

        return [
            'absolute_path' => $uploadPath . DIRECTORY_SEPARATOR . $fileName,
            'relative_path' => $folder . '/' . $fileName,
            'filename' => $fileName,
            'directory' => $uploadPath,
        ];
    }

    public static function directoryCandidates(string $folder): array
    {
        $folder = trim($folder, '/');
        $candidates = [];

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $folder;
        }
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $folder;
        }

        $candidates[] = public_path($folder);

        $parentDir = dirname(base_path());
        $candidates[] = $parentDir . '/public_html/' . $folder;

        return array_values(array_unique(array_filter($candidates)));
    }

    public static function resolveExistingFile(string $relativePath): ?string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }

        $filename = basename($relativePath);
        $folder = trim(dirname($relativePath), '/.');
        if ($folder === '') {
            $folder = 'exam_papers';
        }

        $basePath = base_path();
        $parentDir = dirname($basePath);

        $candidates = [];

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $relativePath;
        }

        $candidates[] = public_path($relativePath);

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relativePath;
        }

        $candidates[] = $parentDir . '/public_html/' . $relativePath;

        foreach (self::directoryCandidates($folder) as $dir) {
            $candidates[] = rtrim($dir, '/') . '/' . $filename;
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
    }

    /**
     * When userImages is writable but exam_papers is not, fix or recreate exam_papers.
     */
    private static function syncPermissionsWithUserImages(string $targetDir, string $userImagesDir): void
    {
        if (!is_dir($userImagesDir)) {
            return;
        }

        $probeUser = $userImagesDir . '/.probe_' . uniqid('', true);
        if (@file_put_contents($probeUser, '1') === false) {
            return;
        }
        @unlink($probeUser);

        $probeTarget = $targetDir . '/.probe_' . uniqid('', true);
        if (@file_put_contents($probeTarget, '1') !== false) {
            @unlink($probeTarget);

            return;
        }

        $userPerms = fileperms($userImagesDir) & 0777;
        if ($userPerms > 0) {
            @chmod($targetDir, $userPerms);
        } else {
            @chmod($targetDir, 0777);
        }

        if (@file_put_contents($probeTarget, '1') !== false) {
            @unlink($probeTarget);

            return;
        }

        if (is_dir($targetDir) && self::isDirectoryEmpty($targetDir)) {
            @rmdir($targetDir);
            @mkdir($targetDir, $userPerms > 0 ? $userPerms : 0777, true);
        }
    }

    private static function isDirectoryEmpty(string $dir): bool
    {
        $handle = @opendir($dir);
        if ($handle === false) {
            return false;
        }

        while (($entry = readdir($handle)) !== false) {
            if ($entry !== '.' && $entry !== '..') {
                closedir($handle);

                return false;
            }
        }

        closedir($handle);

        return true;
    }
}
