<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\OnlineAdmission;
use App\Models\ParentModel;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\ParentPhone;
use App\Support\ParentUserAccount;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OnlineAdmissionService
{
    public function __construct(
        private OnlineAdmissionFormService $formService,
        private OnlineAdmissionSpaceService $spaceService
    ) {
    }

    public function generateApplicationNumber(int $schoolID): string
    {
        do {
            $number = 'ONL/' . $schoolID . '/' . date('Y') . '/' . strtoupper(Str::random(6));
        } while (OnlineAdmission::where('application_number', $number)->exists());

        return $number;
    }

    /**
     * Store upload using the same path resolution as ManageStudentController photos
     * (SCRIPT_FILENAME / DOCUMENT_ROOT / public_path) — works on local, subdomain, cPanel.
     */
    public function storeUploadedImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $stored = PublicUploadPathService::storeUploadedFile($file, 'userImages');

        return $stored['filename'];
    }

    public function storeUploadedFile(?UploadedFile $file): ?string
    {
        return $this->storeUploadedImage($file);
    }

    /**
     * Resolve a file from system or nested custom[field] input.
     */
    private function resolveUploadedFile(Request $request, string $inputKey): ?UploadedFile
    {
        $file = $request->file($inputKey);
        if ($file instanceof UploadedFile) {
            return $file;
        }

        if (str_starts_with($inputKey, 'custom.')) {
            $nestedKey = substr($inputKey, strlen('custom.'));
            $custom = $request->file('custom');
            if (is_array($custom) && isset($custom[$nestedKey]) && $custom[$nestedKey] instanceof UploadedFile) {
                return $custom[$nestedKey];
            }
        }

        return null;
    }

    /**
     * @return array{rules: array, messages: array, custom_keys: array}
     */
    public function buildValidationRules(int $schoolID, int $classID, bool $isUpdate = false): array
    {
        $fields = $this->formService->activeFields($schoolID, $classID);
        $rules = $isUpdate ? [] : ['apply_token' => 'required|string'];
        $messages = [];
        $customKeys = [];
        $systemKeys = collect(OnlineAdmissionFormService::SYSTEM_FIELDS)->pluck('field_key')->all();

        foreach ($fields as $field) {
            if ($field->field_type === 'section') {
                continue;
            }

            $key = $field->field_key;
            if (!in_array($key, $systemKeys, true)) {
                $key = 'custom.' . $key;
                $customKeys[] = $field->field_key;
            }

            $rule = [];
            $isFile = $field->field_type === 'file';
            if ($field->is_required && !($isUpdate && $isFile)) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'text':
                    $rule[] = 'string';
                    $rule[] = 'max:255';
                    break;
                case 'phone':
                    $rule[] = 'string';
                    $rule[] = 'regex:/^255[67]\d{8}$/';
                    $messages[$key . '.regex'] = ($field->label ?? 'Phone') . ' must start with 255 and be a valid Tanzania mobile (e.g. 255712345678).';
                    break;
                case 'textarea':
                    $rule[] = 'string';
                    $rule[] = 'max:5000';
                    break;
                case 'gender':
                    $rule[] = 'in:Male,Female';
                    break;
                case 'select':
                case 'radio':
                    $rule[] = 'string';
                    $rule[] = 'max:255';
                    break;
                case 'checkbox':
                    $rule[] = 'boolean';
                    break;
                case 'file':
                    $rule[] = 'file';
                    $rule[] = 'mimes:pdf,doc,docx,png,jpg,jpeg,webp';
                    $rule[] = 'max:10240';
                    break;
            }

            $rules[$key] = implode('|', $rule);
        }

        return compact('rules', 'messages', 'customKeys');
    }

    public function submit(Request $request, int $schoolID, int $classID, int $academicYearID, ?int $onlineApplicationUserID = null): OnlineAdmission
    {
        $available = $this->spaceService->availableSpaces($schoolID, $classID, $academicYearID);
        if ($available <= 0) {
            throw new \RuntimeException('No admission spaces available for this class.');
        }

        $customResponses = [];
        $customFields = $this->formService->activeFields($schoolID, $classID)
            ->where('is_system', false)
            ->where('field_type', '!=', 'section');

        foreach ($customFields as $field) {
            $inputKey = 'custom.' . $field->field_key;
            if ($field->field_type === 'file') {
                $uploaded = $this->resolveUploadedFile($request, $inputKey);
                if ($uploaded) {
                    $customResponses[$field->field_key] = $this->storeUploadedFile($uploaded);
                }
                continue;
            }
            if ($request->has($inputKey)) {
                $customResponses[$field->field_key] = $request->input($inputKey);
            }
        }

        return DB::transaction(function () use ($request, $schoolID, $classID, $academicYearID, $onlineApplicationUserID, $customResponses) {
            $admission = OnlineAdmission::create([
                'application_number' => $this->generateApplicationNumber($schoolID),
                'online_application_userID' => $onlineApplicationUserID,
                'schoolID' => $schoolID,
                'classID' => $classID,
                'academic_yearID' => $academicYearID,
                'status' => OnlineAdmission::STATUS_PENDING,
                'student_first_name' => $request->input('first_name'),
                'student_middle_name' => $request->input('middle_name'),
                'student_last_name' => $request->input('last_name'),
                'student_gender' => $request->input('gender'),
                'student_photo' => $this->storeUploadedImage($this->resolveUploadedFile($request, 'student_photo')),
                'parent_phone' => ParentPhone::normalize($request->input('parent_phone')) ?? $request->input('parent_phone'),
                'parent_first_name' => $request->input('parent_first_name'),
                'parent_middle_name' => $request->input('parent_middle_name'),
                'parent_last_name' => $request->input('parent_last_name'),
                'parent_gender' => $request->input('parent_gender'),
                'parent_photo' => $this->storeUploadedImage($this->resolveUploadedFile($request, 'parent_photo')),
                'custom_responses' => $customResponses ?: null,
            ]);

            $this->notifySchoolOfNewApplication($admission);

            return $admission;
        });
    }

    /**
     * Update a pending application owned by the applicant.
     */
    public function updatePending(OnlineAdmission $admission, Request $request): OnlineAdmission
    {
        if ($admission->status !== OnlineAdmission::STATUS_PENDING) {
            throw new \RuntimeException('Only pending applications can be edited.');
        }

        $customResponses = is_array($admission->custom_responses) ? $admission->custom_responses : [];
        $customFields = $this->formService->activeFields((int) $admission->schoolID, (int) $admission->classID)
            ->where('is_system', false)
            ->where('field_type', '!=', 'section');

        foreach ($customFields as $field) {
            $inputKey = 'custom.' . $field->field_key;
            if ($field->field_type === 'file') {
                $uploaded = $this->resolveUploadedFile($request, $inputKey);
                if ($uploaded) {
                    if (!empty($customResponses[$field->field_key])) {
                        PublicUploadPathService::deleteIfExists('userImages/' . $customResponses[$field->field_key]);
                    }
                    $customResponses[$field->field_key] = $this->storeUploadedFile($uploaded);
                }
                continue;
            }
            if ($request->has($inputKey)) {
                $customResponses[$field->field_key] = $request->input($inputKey);
            }
        }

        $studentPhoto = $admission->student_photo;
        $studentUpload = $this->resolveUploadedFile($request, 'student_photo');
        if ($studentUpload) {
            if ($studentPhoto) {
                PublicUploadPathService::deleteIfExists('userImages/' . $studentPhoto);
            }
            $studentPhoto = $this->storeUploadedImage($studentUpload);
        }

        $parentPhoto = $admission->parent_photo;
        $parentUpload = $this->resolveUploadedFile($request, 'parent_photo');
        if ($parentUpload) {
            if ($parentPhoto) {
                PublicUploadPathService::deleteIfExists('userImages/' . $parentPhoto);
            }
            $parentPhoto = $this->storeUploadedImage($parentUpload);
        }

        $admission->update([
            'student_first_name' => $request->input('first_name'),
            'student_middle_name' => $request->input('middle_name'),
            'student_last_name' => $request->input('last_name'),
            'student_gender' => $request->input('gender'),
            'student_photo' => $studentPhoto,
            'parent_phone' => ParentPhone::normalize($request->input('parent_phone')) ?? $request->input('parent_phone'),
            'parent_first_name' => $request->input('parent_first_name'),
            'parent_middle_name' => $request->input('parent_middle_name'),
            'parent_last_name' => $request->input('parent_last_name'),
            'parent_gender' => $request->input('parent_gender'),
            'parent_photo' => $parentPhoto,
            'custom_responses' => $customResponses ?: null,
        ]);

        return $admission->fresh();
    }

    /**
     * Delete a pending application owned by the applicant.
     */
    public function deletePending(OnlineAdmission $admission): void
    {
        if ($admission->status !== OnlineAdmission::STATUS_PENDING) {
            throw new \RuntimeException('Only pending applications can be deleted.');
        }

        if ($admission->student_photo) {
            PublicUploadPathService::deleteIfExists('userImages/' . $admission->student_photo);
        }
        if ($admission->parent_photo) {
            PublicUploadPathService::deleteIfExists('userImages/' . $admission->parent_photo);
        }
        if (is_array($admission->custom_responses)) {
            foreach ($admission->custom_responses as $value) {
                if (is_string($value) && $value !== '' && preg_match('/\.(pdf|docx?|png|jpe?g|webp)$/i', $value)) {
                    PublicUploadPathService::deleteIfExists('userImages/' . $value);
                }
            }
        }

        $admission->delete();
    }

    /**
     * SMS school when a new online application is submitted.
     */
    private function notifySchoolOfNewApplication(OnlineAdmission $admission): void
    {
        try {
            $school = School::find($admission->schoolID);
            if (!$school || empty($school->phone)) {
                return;
            }

            $class = ClassModel::find($admission->classID);
            $studentName = trim(
                $admission->student_first_name . ' '
                . ($admission->student_middle_name ? $admission->student_middle_name . ' ' : '')
                . $admission->student_last_name
            );
            $loginUrl = rtrim((string) config('shulexpert.public_url', 'https://shulexpert.com'), '/') . '/login';

            $message = 'ShuleXpert: New online application '
                . $admission->application_number
                . ' for ' . $studentName
                . ($class ? ' (' . $class->class_name . ')' : '')
                . '. Review in Online Admission. Login: ' . $loginUrl;

            (new SmsService())->sendSms($school->phone, $message, $school->senderID ?? null, $school->schoolID ?? null);
        } catch (\Throwable $e) {
            Log::warning('Online admission school notify SMS failed: ' . $e->getMessage());
        }
    }

    public function confirm(OnlineAdmission $admission, ?int $reviewedBy = null): Student
    {
        if ($admission->status !== OnlineAdmission::STATUS_PENDING) {
            throw new \RuntimeException('Only pending applications can be confirmed.');
        }

        $available = $this->spaceService->availableSpaces(
            (int) $admission->schoolID,
            (int) $admission->classID,
            (int) $admission->academic_yearID
        );
        if ($available <= 0) {
            throw new \RuntimeException('No admission spaces remaining for this class.');
        }

        return DB::transaction(function () use ($admission, $reviewedBy) {
            $schoolID = (int) $admission->schoolID;
            $phone = ParentPhone::normalize($admission->parent_phone);

            $parent = ParentPhone::findInSchool($schoolID, $phone);
            $isNewParent = false;

            if (!$parent) {
                $inOther = ParentPhone::findAnywhere($phone, $schoolID);
                if ($inOther) {
                    throw new \RuntimeException('Parent phone is registered in another school.');
                }

                $parent = ParentModel::create([
                    'schoolID' => $schoolID,
                    'first_name' => $admission->parent_first_name,
                    'middle_name' => $admission->parent_middle_name,
                    'last_name' => $admission->parent_last_name,
                    'gender' => $admission->parent_gender,
                    'phone' => $phone,
                    'photo' => $admission->parent_photo,
                ]);
                $isNewParent = true;
            }

            ParentUserAccount::ensure($parent);

            $admissionNumber = $this->generateSchoolAdmissionNumber($schoolID);

            $fingerprintId = $this->generateUniqueFingerprintId();

            $student = Student::create([
                'studentID' => (int) $fingerprintId,
                'schoolID' => $schoolID,
                'subclassID' => null,
                'parentID' => $parent->parentID,
                'first_name' => $admission->student_first_name,
                'middle_name' => $admission->student_middle_name,
                'last_name' => $admission->student_last_name,
                'gender' => $admission->student_gender,
                'admission_number' => $admissionNumber,
                'fingerprint_id' => $fingerprintId,
                'sent_to_device' => false,
                'device_sent_at' => null,
                'fingerprint_capture_count' => 0,
                'admission_date' => now()->toDateString(),
                'photo' => $admission->student_photo,
                'status' => 'Active',
                'nationality' => 'Tanzanian',
                'registration_status' => 'Draft',
            ]);

            $userEmail = $admissionNumber . '@student.local';
            $emailCounter = 1;
            while (User::where('email', $userEmail)->exists()) {
                $userEmail = $admissionNumber . '_' . $emailCounter . '@student.local';
                $emailCounter++;
            }

            User::create([
                'name' => $admissionNumber,
                'email' => $userEmail,
                'password' => Hash::make($admission->student_first_name),
                'user_type' => 'student',
                'fingerprint_id' => $fingerprintId,
            ]);

            $admission->update([
                'status' => OnlineAdmission::STATUS_CONFIRMED,
                'parentID' => $parent->parentID,
                'studentID' => $student->studentID,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => now(),
            ]);

            $this->sendConfirmSms($admission, $student, $parent, $isNewParent);

            return $student;
        });
    }

    public function reject(OnlineAdmission $admission, ?string $reason = null, ?int $reviewedBy = null): void
    {
        if ($admission->status !== OnlineAdmission::STATUS_PENDING) {
            throw new \RuntimeException('Only pending applications can be rejected.');
        }

        $admission->update([
            'status' => OnlineAdmission::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);
    }

    public function cancel(OnlineAdmission $admission): void
    {
        if ($admission->status === OnlineAdmission::STATUS_CONFIRMED) {
            throw new \RuntimeException('Confirmed admissions cannot be cancelled. Contact system admin.');
        }

        $admission->delete();
    }

    private function generateSchoolAdmissionNumber(int $schoolID): string
    {
        $school = School::find($schoolID);
        $currentYear = date('Y');
        $schoolNumber = $school->registration_number ?? ('SCH' . $schoolID);
        $prefix = $schoolNumber . '/';
        $suffix = '/' . $currentYear;

        $lastAdmissionNumber = Student::where('schoolID', $schoolID)
            ->where('admission_number', 'like', $prefix . '%' . $suffix)
            ->lockForUpdate()
            ->orderBy('admission_number', 'desc')
            ->value('admission_number');

        $sequence = 1;
        if ($lastAdmissionNumber && preg_match('/\/(\d+)\/' . $currentYear . '$/', $lastAdmissionNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        $number = $schoolNumber . '/' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;

        while (Student::where('admission_number', $number)->exists() || User::where('name', $number)->exists()) {
            $sequence++;
            $number = $schoolNumber . '/' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;
        }

        return $number;
    }

    private function generateUniqueFingerprintId(): string
    {
        $attempts = 0;
        do {
            $fingerprintId = (string) rand(1000, 9999);
            $attempts++;
            $exists = User::where('fingerprint_id', $fingerprintId)->exists()
                || Student::where('fingerprint_id', $fingerprintId)->exists()
                || Student::where('studentID', $fingerprintId)->exists();
            if (!$exists) {
                return $fingerprintId;
            }
        } while ($attempts < 100);

        throw new \RuntimeException('Unable to generate unique student ID.');
    }

    private function sendConfirmSms(
        OnlineAdmission $admission,
        Student $student,
        ParentModel $parent,
        bool $isNewParent
    ): void {
        if (!$parent->phone) {
            return;
        }

        try {
            $school = School::find($admission->schoolID);
            $sms = new SmsService();

            if ($isNewParent) {
                $sms->sendParentCredentials(
                    $parent->phone,
                    $school->school_name ?? 'School',
                    $parent->phone,
                    $parent->first_name
                );
            }

            $class = ClassModel::find($admission->classID);
            $message = 'Admission confirmed for '
                . trim($student->first_name . ' ' . $student->last_name)
                . ' at ' . ($school->school_name ?? 'school')
                . ($class ? ' (' . $class->class_name . ')' : '')
                . '. Admission No: ' . $student->admission_number
                . '. Please visit the school for enrollment.';

            $sms->sendSms($parent->phone, $message, $school->senderID ?? null, $school->schoolID ?? null);
        } catch (\Throwable $e) {
            Log::warning('Online admission confirm SMS failed: ' . $e->getMessage());
        }
    }
}
