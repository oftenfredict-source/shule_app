<?php

namespace App\Services;

use App\Models\FingerprintAttendanceSetup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FingerprintAttendanceSetupService
{
    private const FOLDER = 'fingerprint_setup';

    public function currentSetup(): ?FingerprintAttendanceSetup
    {
        return FingerprintAttendanceSetup::query()
            ->orderByDesc('setupID')
            ->first();
    }

    public function resolveDownloadUrl(FingerprintAttendanceSetup $setup): ?string
    {
        $basename = basename((string) $setup->file_path);
        if ($basename === '') {
            return null;
        }

        $candidates = [
            $this->uploadDirectory().'/'.$basename,
            public_path(self::FOLDER.'/'.$basename),
        ];

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.self::FOLDER.'/'.$basename;
        }

        foreach ($candidates as $path) {
            if ($path && is_file($path)) {
                return asset(self::FOLDER.'/'.$basename);
            }
        }

        $stored = ltrim((string) $setup->file_path, '/');
        if ($stored !== '' && is_file(public_path($stored))) {
            return asset($stored);
        }

        return null;
    }

    public function storeUploadedFile(UploadedFile $file, ?string $previousPath = null): array
    {
        $dir = $this->uploadDirectory();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'zip');
        $allowed = ['zip', 'exe', 'msi', 'rar', '7z'];
        if (! in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException('Allowed file types: '.implode(', ', $allowed));
        }

        $filename = 'fp_setup_'.time().'_'.Str::random(8).'.'.$ext;
        $file->move($dir, $filename);

        if ($previousPath) {
            $this->deletePhysicalFile($previousPath);
        }

        return [
            'file_path' => self::FOLDER.'/'.$filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => (int) File::size($dir.'/'.$filename),
        ];
    }

    public function deletePhysicalFile(?string $storedPath): void
    {
        if (! $storedPath) {
            return;
        }

        $basename = basename($storedPath);
        $paths = [
            $this->uploadDirectory().'/'.$basename,
            public_path(self::FOLDER.'/'.$basename),
        ];

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $paths[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.self::FOLDER.'/'.$basename;
        }

        foreach ($paths as $path) {
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Same path strategy as school logo upload (script dir, document root, public).
     */
    public function uploadDirectory(): string
    {
        $folder = self::FOLDER;
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $candidates = array_values(array_unique(array_filter([
            $scriptDir.'/'.$folder,
            isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.$folder : null,
            public_path($folder),
            dirname(base_path()).'/public_html/'.$folder,
        ])));

        foreach ($candidates as $candidate) {
            if ($this->ensureWritableDirectory($candidate)) {
                return $candidate;
            }
        }

        $fallback = public_path($folder);
        $this->ensureWritableDirectory($fallback);

        return $fallback;
    }

    private function ensureWritableDirectory(string $dir): bool
    {
        if (! is_dir($dir) && ! @mkdir($dir, 0777, true)) {
            return false;
        }

        $probe = $dir.'/.upload_probe_'.uniqid('', true);
        if (@file_put_contents($probe, '1') !== false) {
            @unlink($probe);

            return true;
        }

        @chmod($dir, 0777);

        if (@file_put_contents($probe, '1') !== false) {
            @unlink($probe);

            return true;
        }

        return false;
    }
}
