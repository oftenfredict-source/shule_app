<?php

namespace App\Http\Controllers;

use App\Models\FingerprintAttendanceSetup;
use App\Services\FingerprintAttendanceSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class FingerprintAttendanceSetupController extends Controller
{
    public function __construct(
        private FingerprintAttendanceSetupService $setupService
    ) {
    }

    private function ensureSuperAdmin()
    {
        if (Session::get('user_type') !== 'SuperAdmin') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        return null;
    }

    public function index()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $setup = $this->setupService->currentSetup();
        if ($setup) {
            $setup->download_url = $this->setupService->resolveDownloadUrl($setup);
            $setup->file_size_human = $this->formatBytes((int) $setup->file_size);
        }

        $phpUploadLimits = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ];

        return view('SuperAdmin.fingerprint_attendance_setup', compact('setup', 'phpUploadLimits'));
    }

    public function store(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $this->bootstrapLargeUpload();

        if ($uploadError = $this->resolveUploadError($request)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $uploadError], 422);
            }

            return back()->with('error', $uploadError)->withInput();
        }

        if ($this->setupService->currentSetup()) {
            $message = 'Setup tayari ipo. Edit au delete kwanza kabla ya ku-upload nyingine.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $validator = Validator::make($request->only(['title', 'version']), [
            'title' => 'required|string|max:200',
            'version' => 'nullable|string|max:50',
        ]);

        $validator->after(function ($validator) use ($request) {
            $file = $request->file('setup_file');
            if (! $file) {
                $validator->errors()->add('setup_file', 'Setup file inahitajika.');

                return;
            }
            if (! $file->isValid()) {
                $validator->errors()->add('setup_file', $this->uploadErrorMessage((int) $file->getError()));

                return;
            }
            $maxKb = 512000;
            if ($file->getSize() > ($maxKb * 1024)) {
                $validator->errors()->add('setup_file', 'File ni kubwa kuliko 500MB.');
            }
        });

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first() ?: 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        try {
            $fileMeta = $this->setupService->storeUploadedFile($request->file('setup_file'));
        } catch (\InvalidArgumentException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Upload failed: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Upload failed: '.$e->getMessage())->withInput();
        }

        FingerprintAttendanceSetup::create([
            'title' => $request->input('title'),
            'version' => $request->input('version'),
            'file_path' => $fileMeta['file_path'],
            'original_filename' => $fileMeta['original_filename'],
            'file_size' => $fileMeta['file_size'],
            'status' => 'active',
            'uploaded_by' => Session::get('userID'),
        ]);

        $message = 'Fingerprint attendance setup uploaded successfully.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('superadmin.fingerprint_attendance_setup.index')
            ->with('success', $message);
    }

    public function update(Request $request, int $setupID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $this->bootstrapLargeUpload();

        $setup = FingerprintAttendanceSetup::findOrFail($setupID);

        if ($request->hasFile('setup_file')) {
            $file = $request->file('setup_file');
            if (! $file->isValid()) {
                $message = $this->uploadErrorMessage((int) $file->getError());
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return back()->with('error', $message)->withInput();
            }
        }

        $validator = Validator::make($request->only(['title', 'version']), [
            'title' => 'required|string|max:200',
            'version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first() ?: 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'title' => $request->input('title'),
            'version' => $request->input('version'),
        ];

        if ($request->hasFile('setup_file')) {
            $file = $request->file('setup_file');
            if ($file->getSize() > (512000 * 1024)) {
                $message = 'File ni kubwa kuliko 500MB.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return back()->with('error', $message)->withInput();
            }

            try {
                $fileMeta = $this->setupService->storeUploadedFile($request->file('setup_file'), $setup->file_path);
                $data['file_path'] = $fileMeta['file_path'];
                $data['original_filename'] = $fileMeta['original_filename'];
                $data['file_size'] = $fileMeta['file_size'];
            } catch (\InvalidArgumentException $e) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }

                return back()->with('error', $e->getMessage())->withInput();
            } catch (\Throwable $e) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Upload failed: '.$e->getMessage()], 500);
                }

                return back()->with('error', 'Upload failed: '.$e->getMessage())->withInput();
            }
        }

        $setup->update($data);

        $message = 'Setup updated successfully.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('superadmin.fingerprint_attendance_setup.index')
            ->with('success', $message);
    }

    public function destroy(int $setupID)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $setup = FingerprintAttendanceSetup::findOrFail($setupID);
        $this->setupService->deletePhysicalFile($setup->file_path);
        $setup->delete();

        return redirect()->route('superadmin.fingerprint_attendance_setup.index')
            ->with('success', 'Setup deleted successfully.');
    }

    public function download(int $setupID)
    {
        $userType = Session::get('user_type');
        if (! $userType) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if (! in_array($userType, ['Admin', 'Staff', 'Teacher', 'SuperAdmin'], true)) {
            abort(403);
        }

        $setup = FingerprintAttendanceSetup::findOrFail($setupID);

        $basename = basename((string) $setup->file_path);
        $paths = [
            $this->setupService->uploadDirectory().'/'.$basename,
            public_path('fingerprint_setup/'.$basename),
        ];

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $paths[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/fingerprint_setup/'.$basename;
        }

        foreach ($paths as $path) {
            if ($path && is_file($path)) {
                return response()->download($path, $setup->original_filename ?: $basename);
            }
        }

        abort(404, 'Setup file not found on server.');
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }

    private function bootstrapLargeUpload(): void
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');
    }

    private function resolveUploadError(Request $request): ?string
    {
        if ($request->hasFile('setup_file')) {
            $file = $request->file('setup_file');
            if ($file && ! $file->isValid()) {
                return $this->uploadErrorMessage((int) $file->getError());
            }

            return null;
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMaxBytes = $this->iniSizeToBytes((string) ini_get('post_max_size'));
        if ($contentLength > 0 && $postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            return 'File ni kubwa sana. Kikomo cha server ni '.ini_get('post_max_size').'.';
        }

        if ($contentLength > 0) {
            return 'Upload haikufika server. Hakikisha file sio kubwa kuliko '.ini_get('upload_max_filesize').'.';
        }

        return 'Setup file inahitajika.';
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File ni kubwa kuliko kikomo cha server ('.ini_get('upload_max_filesize').').',
            UPLOAD_ERR_FORM_SIZE => 'File ni kubwa sana.',
            UPLOAD_ERR_PARTIAL => 'Upload ilikatizwa. Jaribu tena.',
            UPLOAD_ERR_NO_FILE => 'Setup file inahitajika.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Server haiwezi kuhifadhi file. Angalia permissions.',
            UPLOAD_ERR_EXTENSION => 'Aina ya file hii hairuhusiwi na server.',
            default => 'Upload imeshindwa. Jaribu tena.',
        };
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
