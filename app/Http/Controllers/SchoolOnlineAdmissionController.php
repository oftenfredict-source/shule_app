<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\OnlineAdmission;
use App\Models\OnlineAdmissionFormField;
use App\Services\OnlineAdmissionFormService;
use App\Services\OnlineAdmissionService;
use App\Services\OnlineAdmissionSpaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SchoolOnlineAdmissionController extends Controller
{
    private int $defaultAdmissionSpaces = 50;

    public function __construct(
        private OnlineAdmissionFormService $formService,
        private OnlineAdmissionSpaceService $spaceService,
        private OnlineAdmissionService $admissionService,
        private \App\Services\OnlineApplicationApplyTokenService $applyTokens
    ) {
    }

    private function schoolIdOrAbort(): int
    {
        $schoolID = (int) Session::get('schoolID');
        if (!$schoolID) {
            abort(403, 'School session not found.');
        }

        return $schoolID;
    }

    /**
     * Resolve and validate the selected main class for form configuration.
     */
    private function requireClassId(Request $request): int
    {
        $schoolID = $this->schoolIdOrAbort();
        $classID = (int) ($request->input('classID') ?: $request->query('classID'));

        if ($classID <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'classID' => 'Please select a class (main class) first.',
            ]);
        }

        $exists = ClassModel::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('status', 'Active')
            ->exists();

        if (!$exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'classID' => 'Selected class was not found.',
            ]);
        }

        return $classID;
    }

    private function classIdErrorResponse(\Illuminate\Validation\ValidationException $e)
    {
        return response()->json([
            'success' => false,
            'message' => collect($e->errors())->flatten()->first() ?: 'Please select a class first.',
            'errors' => $e->errors(),
        ], 422);
    }

    public function index()
    {
        $user_type = Session::get('user_type');
        if (!$user_type) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = $this->schoolIdOrAbort();
        $this->formService->ensureDefaultsForSchool($schoolID);
        Session::put('online_admissions_last_seen', now()->toDateTimeString());

        $activeYear = $this->spaceService->currentAcademicYearForSchool($schoolID);
        $classes = ClassModel::where('schoolID', $schoolID)->where('status', 'Active')->orderBy('class_name')->get();
        $publicApplicationUrl = $this->applyTokens->schoolUrl($schoolID);

        $this->spaceService->ensureDefaultSpacesForSchool($schoolID, $this->defaultAdmissionSpaces);

        return view('Admin.online_admission', compact('activeYear', 'classes', 'user_type', 'publicApplicationUrl'));
    }

    public function formFields(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }

        return response()->json([
            'success' => true,
            'classID' => $classID,
            'fields' => $this->formService->allFields($schoolID, $classID),
            'sections' => $this->formService->allSections($schoolID, $classID),
            'field_types' => OnlineAdmissionFormService::FIELD_TYPES,
        ]);
    }

    public function formSections(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }

        return response()->json([
            'success' => true,
            'classID' => $classID,
            'sections' => $this->formService->allSections($schoolID, $classID),
        ]);
    }

    public function storeFormSection(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $section = $this->formService->createSection($schoolID, $classID, $request->only(['title', 'description', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'Section created.',
                'section' => $section,
                'sections' => $this->formService->allSections($schoolID, $classID),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updateFormSection(Request $request, $id)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }
        $section = \App\Models\OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $section = $this->formService->updateSection($section, $request->only(['title', 'description', 'is_active', 'sort_order']));

            return response()->json([
                'success' => true,
                'message' => 'Section updated.',
                'section' => $section,
                'sections' => $this->formService->allSections($schoolID, $classID),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function deleteFormSection(Request $request, $id)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }
        $section = \App\Models\OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->findOrFail($id);

        try {
            $this->formService->deleteSection($section);

            return response()->json([
                'success' => true,
                'message' => 'Section deleted.',
                'sections' => $this->formService->allSections($schoolID, $classID),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    /**
     * Save the full form builder (add / edit / reorder / remove) in one request.
     * Pattern matches PangishaPro guest-forms sync.
     */
    public function saveFormFields(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }

        $validator = Validator::make($request->all(), [
            'fields' => 'required|array|min:1',
            'fields.*.id' => 'nullable|integer',
            'fields.*.section_id' => 'nullable|integer',
            'fields.*.field_key' => 'nullable|string|max:80',
            'fields.*.label' => 'required|string|max:200',
            'fields.*.field_type' => 'nullable|string|max:30',
            'fields.*.type' => 'nullable|string|max:30',
            'fields.*.is_required' => 'nullable',
            'fields.*.is_active' => 'nullable',
            'fields.*.options' => 'nullable',
            'fields.*.sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $fields = $this->formService->syncFields($schoolID, $classID, $request->input('fields', []));

            return response()->json([
                'success' => true,
                'message' => 'Admission form saved for the selected class.',
                'classID' => $classID,
                'fields' => $fields,
                'sections' => $this->formService->allSections($schoolID, $classID),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Could not save form.',
            ], 500);
        }
    }

    public function storeFormField(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:200',
            'field_type' => 'required|in:' . implode(',', OnlineAdmissionFormService::FIELD_TYPES),
            'is_required' => 'nullable|boolean',
            'options' => 'nullable|array',
            'options.*' => 'string|max:120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $key = 'custom_' . Str::slug($request->label, '_');
        $base = $key;
        $i = 1;
        while (
            OnlineAdmissionFormField::where('schoolID', $schoolID)
                ->where('classID', $classID)
                ->where('field_key', $key)
                ->exists()
        ) {
            $key = $base . '_' . $i++;
        }

        $maxOrder = (int) OnlineAdmissionFormField::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->max('sort_order');

        $field = OnlineAdmissionFormField::create([
            'schoolID' => $schoolID,
            'classID' => $classID,
            'field_key' => $key,
            'label' => $request->label,
            'field_type' => $request->field_type,
            'options' => in_array($request->field_type, ['select', 'radio'], true)
                ? array_values($request->input('options', []))
                : null,
            'is_required' => (bool) $request->boolean('is_required'),
            'is_system' => false,
            'is_active' => true,
            'sort_order' => $maxOrder + 10,
        ]);

        return response()->json(['success' => true, 'field' => $field]);
    }

    public function updateFormField(Request $request, $id)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }
        $field = OnlineAdmissionFormField::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->findOrFail($id);

        $rules = [
            'label' => 'sometimes|string|max:200',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'options' => 'nullable|array',
        ];

        if (!$field->is_system) {
            $rules['field_type'] = 'sometimes|in:text,textarea,select,radio,checkbox,file,phone,gender';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $field->fill($request->only(['label', 'is_required', 'is_active']));
        if (!$field->is_system && $request->filled('field_type')) {
            $field->field_type = $request->field_type;
        }
        if (in_array($field->field_type, ['select', 'radio'], true)) {
            $field->options = array_values($request->input('options', []));
        }
        $field->save();

        return response()->json(['success' => true, 'field' => $field]);
    }

    public function deleteFormField(Request $request, $id)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->classIdErrorResponse($e);
        }
        $field = OnlineAdmissionFormField::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->findOrFail($id);

        if ($field->is_system) {
            return response()->json(['success' => false, 'message' => 'System fields cannot be deleted.'], 403);
        }

        $field->delete();

        return response()->json(['success' => true]);
    }

    public function admissionSpaces()
    {
        $schoolID = $this->schoolIdOrAbort();
        $activeYear = $this->spaceService->currentAcademicYearForSchool($schoolID);

        $classes = ClassModel::where('schoolID', $schoolID)->where('status', 'Active')->orderBy('class_name')->get();

        $this->spaceService->ensureDefaultSpacesForSchool($schoolID, $this->defaultAdmissionSpaces);

        $rows = $classes->map(function ($class) {
            $availability = $this->spaceService->classAvailability($class);

            return [
                'classID' => $class->classID,
                'class_name' => $class->class_name,
                'spaces' => $availability['allocated_spaces'],
                'used' => $availability['used_spaces'],
                'available' => $availability['available_spaces'],
                'academic_yearID' => $availability['academic_yearID'],
                'academic_year_name' => $availability['academic_year_name'],
            ];
        });

        return response()->json([
            'success' => true,
            'academic_year' => $activeYear,
            'classes' => $rows,
        ]);
    }

    public function saveAdmissionSpaces(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();

        $validator = Validator::make($request->all(), [
            'spaces' => 'required|array',
            'spaces.*' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $this->spaceService->saveSpacesForClasses(
            $schoolID,
            $request->input('spaces', [])
        );

        return response()->json(['success' => true, 'message' => 'Admission spaces saved.']);
    }

    public function applications(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();

        $query = OnlineAdmission::with(['class'])
            ->where('schoolID', $schoolID)
            ->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('classID')) {
            $query->where('classID', (int) $request->classID);
        }

        $items = $query->get()->map(function (OnlineAdmission $row) {
            return [
                'id' => $row->id,
                'application_number' => $row->application_number,
                'student_name' => trim($row->student_first_name . ' ' . ($row->student_middle_name ? $row->student_middle_name . ' ' : '') . $row->student_last_name),
                'parent_phone' => $row->parent_phone,
                'class_name' => $row->class?->class_name,
                'status' => $row->status,
                'created_at' => $row->created_at?->format('d M Y H:i'),
            ];
        });

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function showApplication($id)
    {
        $schoolID = $this->schoolIdOrAbort();
        $admission = OnlineAdmission::with(['class', 'academicYear', 'school'])
            ->where('schoolID', $schoolID)
            ->findOrFail($id);

        $detail = $this->buildApplicationDetailView($admission);

        return response()->json([
            'success' => true,
            'data' => $admission,
            'meta' => $detail['meta'],
            'sections' => $detail['sections'],
            'custom_fields' => $detail['flat_fields'],
            'attachments' => $detail['attachments'],
        ]);
    }

    public function downloadApplicationPdf($id)
    {
        $schoolID = $this->schoolIdOrAbort();
        $admission = OnlineAdmission::with(['class', 'academicYear', 'school'])
            ->where('schoolID', $schoolID)
            ->findOrFail($id);

        $detail = $this->buildApplicationDetailView($admission);
        $school = $admission->school ?? \App\Models\School::find($schoolID);

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('Admin.pdf.online_admission_application', [
                'school' => $school,
                'admission' => $admission,
                'meta' => $detail['meta'],
                'sections' => $detail['sections'],
                'generatedAt' => now()->format('d M Y H:i'),
            ])->setPaper('a4', 'portrait');

            $safeRef = preg_replace('/[^\w\-]+/', '_', (string) $admission->application_number);
            $filename = 'Online_Application_' . $safeRef . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Online admission PDF failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Could not generate application PDF.',
            ], 500);
        }
    }

    /**
     * Arrange application answers by form sections for modal + PDF.
     *
     * @return array{meta:array,sections:array,flat_fields:array,attachments:array}
     */
    private function buildApplicationDetailView(OnlineAdmission $admission): array
    {
        $schoolID = (int) $admission->schoolID;
        $classID = (int) $admission->classID;
        $this->formService->ensureDefaults($schoolID, $classID);
        $formSections = $this->formService->activeSectionsWithFields($schoolID, $classID);

        $systemValues = [
            'first_name' => $admission->student_first_name,
            'middle_name' => $admission->student_middle_name,
            'last_name' => $admission->student_last_name,
            'gender' => $admission->student_gender,
            'student_photo' => $admission->student_photo,
            'parent_phone' => $admission->parent_phone,
            'parent_first_name' => $admission->parent_first_name,
            'parent_middle_name' => $admission->parent_middle_name,
            'parent_last_name' => $admission->parent_last_name,
            'parent_gender' => $admission->parent_gender,
            'parent_photo' => $admission->parent_photo,
        ];
        $custom = is_array($admission->custom_responses) ? $admission->custom_responses : [];

        $sections = [];
        $flatFields = [];
        $attachments = [];
        $seenKeys = [];

        foreach ($formSections as $section) {
            $fieldsOut = [];
            foreach ($section->fields as $field) {
                if ($field->field_type === 'section') {
                    continue;
                }
                $key = $field->field_key;
                $seenKeys[$key] = true;
                $raw = array_key_exists($key, $systemValues)
                    ? $systemValues[$key]
                    : ($custom[$key] ?? null);

                $item = $this->formatDetailField($key, $field->label, $field->field_type, $raw);
                $fieldsOut[] = $item;
                $flatFields[] = $item;
                if (!empty($item['is_file']) && !empty($item['url'])) {
                    $attachments[] = $item;
                }
            }
            if ($fieldsOut !== []) {
                $sections[] = [
                    'title' => $section->title,
                    'description' => $section->description,
                    'fields' => $fieldsOut,
                ];
            }
        }

        // Any leftover custom answers not in current form definition
        $leftover = [];
        foreach ($custom as $key => $raw) {
            if (isset($seenKeys[$key])) {
                continue;
            }
            $label = Str::title(str_replace(['_', '-'], ' ', (string) $key));
            $item = $this->formatDetailField((string) $key, $label, $this->isUploadedFilename($raw) ? 'file' : 'text', $raw);
            $leftover[] = $item;
            $flatFields[] = $item;
            if (!empty($item['is_file']) && !empty($item['url'])) {
                $attachments[] = $item;
            }
        }
        if ($leftover !== []) {
            $sections[] = [
                'title' => 'Additional responses',
                'description' => null,
                'fields' => $leftover,
            ];
        }

        $studentName = trim(
            $admission->student_first_name . ' '
            . ($admission->student_middle_name ? $admission->student_middle_name . ' ' : '')
            . $admission->student_last_name
        );

        return [
            'meta' => [
                'application_number' => $admission->application_number,
                'status' => $admission->status,
                'student_name' => $studentName,
                'class_name' => $admission->class->class_name ?? '',
                'academic_year' => $admission->academicYear->year_name
                    ?? $admission->academicYear->year
                    ?? '',
                'submitted_at' => $admission->created_at?->format('d M Y H:i'),
                'parent_name' => trim(($admission->parent_first_name ?? '') . ' ' . ($admission->parent_last_name ?? '')),
                'parent_phone' => $admission->parent_phone,
            ],
            'sections' => $sections,
            'flat_fields' => $flatFields,
            'attachments' => $attachments,
        ];
    }

    private function formatDetailField(string $key, string $label, string $fieldType, mixed $raw): array
    {
        if ($fieldType === 'file' || $this->isUploadedFilename($raw)) {
            if ($raw) {
                return array_merge($this->formatUploadAttachment($key, $label, (string) $raw), [
                    'is_file' => true,
                    'value' => null,
                    'field_type' => 'file',
                ]);
            }

            return [
                'key' => $key,
                'label' => $label,
                'field_type' => 'file',
                'is_file' => true,
                'value' => '-',
                'filename' => null,
                'url' => null,
                'download_url' => null,
                'is_image' => false,
            ];
        }

        $value = '-';
        if ($raw !== null && $raw !== '') {
            if (is_bool($raw)) {
                $value = $raw ? 'Yes' : 'No';
            } elseif ($fieldType === 'checkbox') {
                $value = ((string) $raw === '1' || $raw === true) ? 'Yes' : 'No';
            } else {
                $value = (string) $raw;
            }
        }

        return [
            'key' => $key,
            'label' => $label,
            'field_type' => $fieldType,
            'is_file' => false,
            'value' => $value,
            'filename' => null,
            'url' => null,
            'download_url' => null,
            'is_image' => false,
        ];
    }

    private function isUploadedFilename(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        return (bool) preg_match('/\.(pdf|docx?|png|jpe?g|webp)$/i', $value);
    }

    /**
     * @return array{key:string,label:string,filename:string,url:string,download_url:string,is_image:bool}
     */
    private function formatUploadAttachment(string $key, string $label, string $filename): array
    {
        $filename = basename($filename);
        $url = url('userImages/' . $filename);
        $isImage = (bool) preg_match('/\.(png|jpe?g|webp)$/i', $filename);

        return [
            'key' => $key,
            'label' => $label,
            'filename' => $filename,
            'url' => $url,
            'download_url' => $url,
            'is_image' => $isImage,
        ];
    }

    public function confirmApplication($id)
    {
        $schoolID = $this->schoolIdOrAbort();
        $admission = OnlineAdmission::where('schoolID', $schoolID)->findOrFail($id);

        try {
            $student = $this->admissionService->confirm($admission, (int) Session::get('userID'));

            return response()->json([
                'success' => true,
                'message' => 'Admission confirmed. Student registered (not enrolled). SMS sent to parent.',
                'admission_number' => $student->admission_number,
                'studentID' => $student->studentID,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function rejectApplication(Request $request, $id)
    {
        $schoolID = $this->schoolIdOrAbort();
        $admission = OnlineAdmission::where('schoolID', $schoolID)->findOrFail($id);

        try {
            $this->admissionService->reject($admission, $request->input('reason'), (int) Session::get('userID'));

            return response()->json(['success' => true, 'message' => 'Application rejected.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelApplication($id)
    {
        $schoolID = $this->schoolIdOrAbort();
        $admission = OnlineAdmission::where('schoolID', $schoolID)->findOrFail($id);

        try {
            $this->admissionService->cancel($admission);

            return response()->json(['success' => true, 'message' => 'Application deleted.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function previewForm(Request $request)
    {
        $schoolID = $this->schoolIdOrAbort();
        try {
            $classID = $this->requireClassId($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response(
                '<div class="alert alert-warning mb-0">Please select a class (main class) first to preview its admission form.</div>',
                422
            );
        }

        $formSections = $this->formService->activeSectionsWithFields($schoolID, $classID);

        return view('partials.online_admission_form_preview', compact('formSections'));
    }
}
