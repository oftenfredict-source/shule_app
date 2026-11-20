<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ManageTeachersController;
use App\Http\Controllers\TeachersController;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hub for Account → Profile (web: /teacher/profile).
 * Photo / PDF / signature stay as bridges; update + password go through web logic.
 */
class TeacherProfileMobileApiController extends Controller
{
    /** GET /profile */
    public function show()
    {
        $result = $this->callWeb(TeachersController::class, 'profile');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Profile is not available.');
        }

        $d = $result->getData();
        $teacher = $d['teacher'] ?? null;
        $user = $d['user'] ?? null;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
        }

        $imageUrl = $teacher->image
            ? asset('userImages/'.$teacher->image)
            : (($teacher->gender ?? '') === 'Female'
                ? asset('images/female.png')
                : asset('images/male.png'));

        $signatureUrl = filled($teacher->signature ?? null)
            ? asset($teacher->signature)
            : null;

        $pct = (int) ($teacher->tange_completion_percent ?? 0);
        $retire = $teacher->retirement_alert ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'My Profile',
                'identity' => [
                    'teacher_id' => (int) $teacher->id,
                    'full_name' => trim(implode(' ', array_filter([
                        $teacher->first_name,
                        $teacher->middle_name,
                        $teacher->last_name,
                    ]))),
                    'first_name' => $teacher->first_name,
                    'middle_name' => $teacher->middle_name,
                    'last_name' => $teacher->last_name,
                    'employee_number' => $teacher->employee_number,
                    'position' => $teacher->position,
                    'status' => $teacher->status,
                    'image_url' => $imageUrl,
                    'has_custom_image' => filled($teacher->image),
                ],
                'tange' => [
                    'completion_percent' => $pct,
                    'is_complete' => $pct >= 100,
                    'missing_fields' => array_values($teacher->tange_missing_fields ?? []),
                    'retirement_alert' => $retire,
                ],
                'personal' => [
                    'gender' => $teacher->gender,
                    'age' => $teacher->age,
                    'date_of_birth' => $this->fmtDate($teacher->date_of_birth),
                    'national_id' => $teacher->national_id,
                    'phone_number' => $teacher->phone_number,
                    'email' => $teacher->email,
                    'address' => $teacher->address,
                    'bank_account_number' => $teacher->bank_account_number ?? null,
                    'status' => $teacher->status,
                ],
                'employment' => [
                    'employee_number' => $teacher->employee_number,
                    'tsc_number' => $teacher->tsc_number,
                    'establishment_rank' => $teacher->establishment_rank,
                    'salary_scale' => $teacher->salary_scale,
                    'position' => $teacher->position,
                    'work_station' => $teacher->work_station,
                    'qualification' => $teacher->qualification,
                    'years_of_experience' => $teacher->years_of_experience,
                    'experience' => $teacher->experience,
                    'specialization' => $teacher->specialization,
                    'subjects_teaching' => $teacher->subjects_teaching,
                ],
                'service_timeline' => [
                    'date_hired' => $this->fmtDate($teacher->date_hired),
                    'date_confirmed' => $this->fmtDate($teacher->date_confirmed),
                    'date_current_rank' => $this->fmtDate($teacher->date_current_rank),
                    'date_of_retirement' => $this->fmtDate($teacher->date_of_retirement),
                ],
                'account' => [
                    'username' => $user->name ?? $teacher->employee_number,
                    'has_user_account' => (bool) $user,
                    'has_signature' => filled($teacher->signature ?? null),
                    'signature_url' => $signatureUrl,
                ],
                'editable_fields' => [
                    'first_name', 'middle_name', 'last_name', 'gender',
                    'phone_number', 'email', 'national_id', 'bank_account_number',
                    'employee_number', 'tsc_number', 'establishment_rank', 'salary_scale',
                    'position', 'work_station', 'date_of_birth', 'date_hired',
                    'date_confirmed', 'date_current_rank', 'date_of_retirement',
                    'qualification', 'specialization', 'subjects_teaching', 'address',
                    'image',
                ],
                'actions' => [
                    'update' => 'POST /profile/update',
                    'update_photo' => 'POST /profile/image',
                    'download_tange_pdf' => 'GET /profile/pdf',
                    'change_password' => 'POST /password/change',
                    'upload_signature' => 'POST /signature',
                    'delete_signature' => 'POST /signature/delete',
                ],
            ],
        ]);
    }

    /**
     * POST /profile/update — same as website Edit Profile (update_teacher + TANGE).
     * multipart/form-data or JSON. Auto-fills teacher_id / employee_number / status if omitted.
     */
    public function update(Request $request)
    {
        $teacherID = Session::get('teacherID');
        $schoolID = Session::get('schoolID');
        if (!$teacherID || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $teacher = \App\Models\Teacher::where('id', $teacherID)
            ->where('schoolID', $schoolID)
            ->first();
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
        }

        if (!$request->filled('teacher_id')) {
            $request->merge(['teacher_id' => $teacher->id]);
        }
        if (!$request->filled('employee_number')) {
            $request->merge(['employee_number' => $teacher->employee_number]);
        }
        if (!$request->filled('status')) {
            $request->merge(['status' => $teacher->status ?? 'Active']);
        }
        if (!$request->filled('employee_number_mode')) {
            $request->merge(['employee_number_mode' => 'custom']);
        }

        // Self-update must target the logged-in teacher only.
        if ((int) $request->input('teacher_id') !== (int) $teacher->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->callWeb(ManageTeachersController::class, 'update_teacher');
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return $this->forbiddenOrPassthrough($result, 'Failed to update profile.');
    }

    /**
     * POST /password/change — website rules (min 8 + complexity).
     * Accepts confirm_password or new_password_confirmation.
     */
    public function changePassword(Request $request)
    {
        if ($request->filled('confirm_password') && !$request->filled('new_password_confirmation')) {
            $request->merge(['new_password_confirmation' => $request->input('confirm_password')]);
        }

        $result = $this->callWeb(TeachersController::class, 'changePassword');
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return $this->forbiddenOrPassthrough($result, 'Failed to change password.');
    }

    private function fmtDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)
                ->timezone(config('app.timezone', 'Africa/Dar_es_Salaam'))
                ->format('Y-m-d');
        } catch (\Throwable $e) {
            return is_string($value) ? substr($value, 0, 10) : null;
        }
    }

    private function callWeb(string $class, string $method, array $params = [])
    {
        try {
            return app()->call([app($class), $method], $params);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    private function forbiddenOrPassthrough($result, string $fallbackMessage)
    {
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            $error = Session::get('error') ?: $fallbackMessage;
            $path = (string) parse_url($result->getTargetUrl(), PHP_URL_PATH);
            $status = str_ends_with($path, '/login') ? 403 : 422;

            return response()->json(['success' => false, 'message' => $error], $status);
        }

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json(['success' => false, 'message' => $fallbackMessage], 403);
    }
}
