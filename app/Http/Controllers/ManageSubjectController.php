<?php

namespace App\Http\Controllers;

use App\Models\SchoolSubject;
use App\Models\SchoolSubjectComponent;
use App\Models\ClassSubject;
use App\Models\ClassSubjectComponentTeacher;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Models\SubjectElector;
use App\Models\Student;
use App\Models\School;
use App\Services\SmsService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageSubjectController extends Controller
{
    /**
     * Check if user has a specific permission
     */
    private function hasPermission($permissionName)
    {
        $userType = Session::get('user_type');
        
        // Admin has all permissions
        if ($userType === 'Admin') {
            return true;
        }
        
        // For teachers, check their role permissions
        if ($userType === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if (!$teacherID) {
                return false;
            }
            
            // Get teacher's roles
            $roles = DB::table('teachers')
                ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.teacher_id', $teacherID)
                ->select('roles.id as roleID')
                ->get();
            
            if ($roles->count() === 0) {
                return false;
            }
            
            $roleIds = $roles->pluck('roleID')->toArray();
            
            // Define permission aliases for backward compatibility and hierarchy
            $aliases = $this->subjectPermissionAliases();

            $checkPermissions = [$permissionName];
            if (isset($aliases[$permissionName])) {
                $checkPermissions = array_unique(array_merge($checkPermissions, $aliases[$permissionName]));
            }

            // Check if any role has any of these permissions
            $hasPermission = DB::table('permissions')
                ->whereIn('role_id', $roleIds)
                ->whereIn('name', $checkPermissions)
                ->exists();
            
            return $hasPermission;
        }

        // For staff, check profession permissions (same aliases as teachers)
        if ($userType === 'Staff') {
            $staffID = Session::get('staffID');
            if (!$staffID) {
                return false;
            }

            $professionId = DB::table('other_staff')
                ->where('id', $staffID)
                ->value('profession_id');

            if (!$professionId) {
                return false;
            }

            $aliases = $this->subjectPermissionAliases();
            $checkPermissions = [$permissionName];
            if (isset($aliases[$permissionName])) {
                $checkPermissions = array_unique(array_merge($checkPermissions, $aliases[$permissionName]));
            }

            return DB::table('staff_permissions')
                ->where('profession_id', $professionId)
                ->whereIn('name', $checkPermissions)
                ->exists();
        }
        
        return false;
    }

    /**
     * Subject permission aliases / hierarchy (Blade + JS must stay aligned).
     */
    private function subjectPermissionAliases(): array
    {
        return [
            'create_subject' => ['create_subject', 'subject_create', 'subject_update', 'subject_delete'],
            'edit_subject' => ['edit_subject', 'update_subject', 'subject_update', 'subject_delete', 'update_class_subject'],
            'delete_subject' => ['delete_subject', 'subject_delete', 'delete_class_subject'],
            'activate_subject' => ['activate_subject', 'activate_class_subject', 'update_subject', 'subject_update', 'subject_delete', 'edit_subject'],
            'view_class_subjects' => [
                'view_class_subjects', 'view_subject', 'subject_read_only',
                'subject_create', 'subject_update', 'subject_delete',
                'create_class_subject', 'update_class_subject', 'delete_class_subject', 'activate_class_subject',
                'create_subject', 'edit_subject', 'update_subject', 'delete_subject', 'manage_class_subject',
            ],
            'create_class_subject' => ['create_class_subject', 'subject_create', 'subject_update', 'subject_delete', 'create_subject'],
            'update_class_subject' => ['update_class_subject', 'subject_update', 'subject_delete', 'edit_subject', 'update_subject'],
            'delete_class_subject' => ['delete_class_subject', 'subject_delete', 'delete_subject'],
            'activate_class_subject' => ['activate_class_subject', 'activate_subject', 'subject_update', 'subject_delete', 'update_class_subject', 'update_subject', 'edit_subject'],
            'approve_created_subject' => ['approve_created_subject', 'subject_update', 'subject_delete'],
        ];
    }

    /**
     * Replace duo/paper components for a school subject (A-Level Secondary).
     */
    private function syncSubjectComponents(SchoolSubject $subject, array $components): void
    {
        $subject->components()->delete();

        $order = 0;
        foreach ($components as $component) {
            $name = trim((string) ($component['component_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $maxMarks = (int) ($component['max_marks'] ?? 100);
            if ($maxMarks < 1) {
                $maxMarks = 1;
            }
            if ($maxMarks > 100) {
                $maxMarks = 100;
            }

            SchoolSubjectComponent::create([
                'subjectID' => $subject->subjectID,
                'component_name' => $name,
                'max_marks' => $maxMarks,
                'sort_order' => $order++,
            ]);
        }
    }

    /**
     * Validate duo paper teacher assignments. Returns normalized list or null on hard error.
     */
    private function normalizeComponentTeacherAssignments(
        SchoolSubject $subject,
        array $componentAssignments,
        $schoolID,
        int $index,
        array &$errors
    ): ?array {
        $validComponentIDs = $subject->components->pluck('componentID')->map(fn ($id) => (int) $id)->all();
        $byComponent = [];

        foreach ($componentAssignments as $row) {
            $componentID = (int) ($row['componentID'] ?? 0);
            if (!$componentID || !in_array($componentID, $validComponentIDs, true)) {
                $errors[] = "Subject " . ($index + 1) . ": Invalid paper/component selected.";
                return null;
            }

            $teacherID = $row['teacherID'] ?? null;
            if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                $teacherID = null;
            } else {
                $teacherID = (int) $teacherID;
                $teacher = Teacher::find($teacherID);
                if (!$teacher || $teacher->schoolID != $schoolID) {
                    $errors[] = "Subject " . ($index + 1) . ": Paper teacher not found or does not belong to this school.";
                    return null;
                }
            }

            $byComponent[$componentID] = $teacherID;
        }

        // Ensure every defined paper has a row (teacher may be null)
        $normalized = [];
        foreach ($validComponentIDs as $componentID) {
            $normalized[] = [
                'componentID' => $componentID,
                'teacherID' => $byComponent[$componentID] ?? null,
            ];
        }

        return $normalized;
    }

    private function syncClassSubjectComponentTeachers(
        ClassSubject $classSubject,
        SchoolSubject $subject,
        array $componentAssignments
    ): void {
        ClassSubjectComponentTeacher::where('class_subjectID', $classSubject->class_subjectID)->delete();

        $validComponentIDs = $subject->components->pluck('componentID')->map(fn ($id) => (int) $id)->all();

        foreach ($componentAssignments as $row) {
            $componentID = (int) ($row['componentID'] ?? 0);
            if (!$componentID || !in_array($componentID, $validComponentIDs, true)) {
                continue;
            }

            $teacherID = $row['teacherID'] ?? null;
            if ($teacherID === null || $teacherID === '' || $teacherID === '0') {
                $teacherID = null;
            } else {
                $teacherID = (int) $teacherID;
            }

            ClassSubjectComponentTeacher::create([
                'class_subjectID' => $classSubject->class_subjectID,
                'componentID' => $componentID,
                'teacherID' => $teacherID,
            ]);
        }
    }

    public function manageSubjects()
    {
         $user = Session::get('user_type');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = Session::get('schoolID');

        // Get all school subjects (with A-Level duo components when present)
        $schoolSubjects = SchoolSubject::where('schoolID', $schoolID)
            ->with('components')
            ->orderBy('subject_name')
            ->get();

        // Get all subclasses for dropdown with combinations
        // Use Eloquent to get subclasses with relationships
        $allSubclasses = \App\Models\Subclass::with(['class', 'combie.subjects.components'])
            ->whereHas('class', function($query) use ($schoolID) {
                $query->where('schoolID', $schoolID);
            })
            ->get()
            ->groupBy('classID');
        
        // Filter out default subclasses and format display names
        $subclasses = collect();
        foreach ($allSubclasses as $classID => $classSubclasses) {
            // If class has only one subclass and it's default (empty name), use it but show only class name
            if ($classSubclasses->count() === 1) {
                $subclass = $classSubclasses->first();
                if (trim($subclass->subclass_name) === '') {
                    // This is a default subclass - include it but with display name as class name only
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class->class_name,
                        'combieID' => $subclass->combieID,
                        'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                        'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                    ]);
                } else {
                    // Single subclass with name - show class_name + subclass_name
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $subclass->class->class_name . ' ' . $subclass->subclass_name,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class->class_name,
                        'combieID' => $subclass->combieID,
                        'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                        'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                    ]);
                }
            } else {
                // Multiple subclasses - show all with class_name + subclass_name
                foreach ($classSubclasses as $subclass) {
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = empty($subclassName) 
                        ? $subclass->class->class_name 
                        : $subclass->class->class_name . ' ' . $subclassName;
                    
                    $subclasses->push((object)[
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'display_name' => $displayName,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class->class_name,
                        'combieID' => $subclass->combieID,
                        'combie_name' => $subclass->combie ? $subclass->combie->combie_name : null,
                        'combie_code' => $subclass->combie ? $subclass->combie->combie_code : null,
                    ]);
                }
            }
        }
        
        // Sort by class_name then subclass_name
        $subclasses = $subclasses->sortBy(function($item) {
            return $item->class_name . ' ' . $item->subclass_name;
        })->values();

        // Get all teachers for dropdown
        $teachers = Teacher::where('schoolID', $schoolID)->get();

        // Get school details for conditional display
        $school_details = \App\Models\School::find($schoolID);

        // JS payloads for Add Class Subject searchable selects / combie autofill
        $activeSchoolSubjectsJson = $schoolSubjects
            ->filter(function ($s) {
                return strcasecmp((string) ($s->status ?? ''), 'Active') === 0;
            })
            ->map(function ($s) {
                return [
                    'subjectID' => (int) $s->subjectID,
                    'subject_name' => $s->subject_name,
                    'subject_code' => $s->subject_code,
                    'is_duo' => (bool) ($s->is_duo ?? false),
                    'components' => $s->components
                        ->sortBy('sort_order')
                        ->values()
                        ->map(function ($c) {
                            return [
                                'componentID' => (int) $c->componentID,
                                'component_name' => $c->component_name,
                                'max_marks' => (int) $c->max_marks,
                                'sort_order' => (int) $c->sort_order,
                            ];
                        })
                        ->all(),
                ];
            })
            ->values();

        $teacherOptionsJson = $teachers->map(function ($t) {
            return [
                'id' => (int) $t->id,
                'label' => trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? '')) . ' (' . ($t->employee_number ?? '') . ')',
            ];
        })->values();

        return view('Admin.manageSubject', compact(
            'schoolSubjects',
            'subclasses',
            'teachers',
            'school_details',
            'activeSchoolSubjectsJson',
            'teacherOptionsJson'
        ));
    }

    /**
     * Realtime check: subject_code unique within current school only.
     */
    public function check_subject_code(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['available' => false, 'message' => 'School not found'], 400);
        }

        $code = trim((string) $request->input('subject_code', ''));
        $excludeId = (int) $request->input('exclude_subjectID', 0);

        if ($code === '') {
            return response()->json([
                'available' => true,
                'empty' => true,
                'message' => 'Subject code is optional',
            ]);
        }

        $query = SchoolSubject::where('schoolID', $schoolID)
            ->whereRaw('LOWER(TRIM(subject_code)) = ?', [strtolower($code)]);

        if ($excludeId > 0) {
            $query->where('subjectID', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'empty' => false,
            'message' => $exists
                ? 'Subject code "' . $code . '" already exists for this school.'
                : 'Subject code is available.',
        ]);
    }

    public function save_school_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('create_subject')) {
            return response()->json([
                'error' => 'You do not have permission to create subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Check if request has multiple subjects or single subject (backward compatibility)
            $subjects = $request->has('subjects') && is_array($request->subjects) 
                ? $request->subjects 
                : [[
                    'subject_name' => $request->subject_name,
                    'subject_code' => $request->subject_code,
                    'status' => $request->status,
                    'student_status' => $request->student_status,
                    'is_duo' => $request->boolean('is_duo'),
                    'components' => $request->input('components', []),
                ]];

            $createdSubjects = [];
            $errors = [];
            $hasApprovalPermission = $this->hasPermission('approve_created_subject');
            $school = School::find($schoolID);
            $isSecondarySchool = $school && strcasecmp((string) $school->school_type, 'Secondary') === 0;

            foreach ($subjects as $index => $subjectData) {
                // Validate each subject
                $validator = Validator::make($subjectData, [
                    'subject_name' => 'required|string|max:100',
                    'subject_code' => 'nullable|string|max:20',
                    'status' => 'required|in:Active,Inactive',
                    'student_status' => 'nullable|in:Required,Optional',
                    'is_duo' => 'nullable', // normalized below (jQuery may send "true"/"false"/0/1)
                    'components' => 'nullable|array',
                    'components.*.component_name' => 'nullable|string|max:100',
                    'components.*.max_marks' => 'nullable|integer|min:1|max:100',
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->toArray() as $field => $messages) {
                        $errors[] = "Subject " . ($index + 1) . ": " . $messages[0];
                    }
                    continue;
                }

                // Subject name may duplicate (e.g. Physics O-Level vs A-Level).
                // Subject code must be unique within this school when provided.
                $subjectCode = trim((string) ($subjectData['subject_code'] ?? ''));
                if ($subjectCode !== '') {
                    $existingCode = SchoolSubject::where('schoolID', $schoolID)
                        ->whereRaw('LOWER(TRIM(subject_code)) = ?', [strtolower($subjectCode)])
                        ->first();
                    if ($existingCode) {
                        $errors[] = "Subject " . ($index + 1) . ": Subject code '" . $subjectCode . "' already exists for this school.";
                        continue;
                    }
                    // Also block duplicates within the same submit batch
                    foreach ($createdSubjects as $created) {
                        if (strcasecmp(trim((string) ($created->subject_code ?? '')), $subjectCode) === 0) {
                            $errors[] = "Subject " . ($index + 1) . ": Subject code '" . $subjectCode . "' is duplicated in this form.";
                            continue 2;
                        }
                    }
                }

                $isDuo = $isSecondarySchool && (
                    filter_var($subjectData['is_duo'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    || ($subjectData['is_duo'] ?? '') === '1'
                    || ($subjectData['is_duo'] ?? '') === 1
                );
                $components = is_array($subjectData['components'] ?? null) ? $subjectData['components'] : [];

                if ($isDuo && count($components) < 2) {
                    $errors[] = "Subject " . ($index + 1) . ": Duo subject needs at least 2 papers (e.g. PHY1, PHY2).";
                    continue;
                }

                // Check if user has approval permission - if yes, use requested status, otherwise set to Inactive
                $status = $hasApprovalPermission ? ($subjectData['status'] ?? 'Active') : 'Inactive';

                try {
                    DB::beginTransaction();

                    $subject = SchoolSubject::create([
                        'schoolID' => $schoolID,
                        'subject_name' => $subjectData['subject_name'],
                        'subject_code' => $subjectCode !== '' ? $subjectCode : null,
                        'status' => $status,
                        'is_duo' => $isDuo,
                    ]);

                    if ($isDuo) {
                        $this->syncSubjectComponents($subject, $components);
                    }

                    DB::commit();

                    $subject->load('components');
                    $createdSubjects[] = $subject;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Subject " . ($index + 1) . ": Failed to create - " . $e->getMessage();
                }
            }

            // Return response
            if (count($errors) > 0 && count($createdSubjects) === 0) {
                // All failed
                return response()->json([
                    'error' => implode('<br>', $errors)
                ], 400);
            } elseif (count($errors) > 0) {
                // Some succeeded, some failed
                $successCount = count($createdSubjects);
                $errorCount = count($errors);
                return response()->json([
                    'success' => $successCount . ' subject(s) created successfully. ' . $errorCount . ' subject(s) failed.',
                    'errors' => $errors,
                    'subjects' => $createdSubjects
                ], 200);
            } else {
                // All succeeded
                $subjectNames = array_map(function($s) { return $s->subject_name; }, $createdSubjects);
                return response()->json([
                    'success' => count($createdSubjects) . ' subject(s) created successfully: ' . implode(', ', $subjectNames),
                    'subjects' => $createdSubjects
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_school_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('edit_subject')) {
            return response()->json([
                'error' => 'You do not have permission to edit subjects.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'subjectID' => 'required|exists:school_subjects,subjectID',
                'subject_name' => 'required|string|max:100',
                'subject_code' => 'nullable|string|max:20',
                'status' => 'required|in:Active,Inactive',
                'is_duo' => 'nullable', // normalized below
                'components' => 'nullable|array',
                'components.*.component_name' => 'nullable|string|max:100',
                'components.*.max_marks' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subject = SchoolSubject::where('subjectID', $request->subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'error' => 'Subject not found or does not belong to this school.'
                ], 404);
            }

            // Subject code must be unique within this school (name may duplicate)
            $subjectCode = trim((string) ($request->subject_code ?? ''));
            if ($subjectCode !== '') {
                $existingCode = SchoolSubject::where('schoolID', $schoolID)
                    ->whereRaw('LOWER(TRIM(subject_code)) = ?', [strtolower($subjectCode)])
                    ->where('subjectID', '!=', $request->subjectID)
                    ->first();
                if ($existingCode) {
                    return response()->json([
                        'error' => 'Subject code "' . $subjectCode . '" already exists for this school.'
                    ], 400);
                }
            }

            $school = School::find($schoolID);
            $isSecondarySchool = $school && strcasecmp((string) $school->school_type, 'Secondary') === 0;
            $isDuo = $isSecondarySchool && $request->boolean('is_duo');
            $components = is_array($request->components) ? $request->components : [];

            if ($isDuo && count($components) < 2) {
                return response()->json([
                    'error' => 'Duo subject needs at least 2 papers (e.g. PHY1, PHY2).'
                ], 422);
            }

            DB::beginTransaction();
            try {
                $subject->update([
                    'subject_name' => $request->subject_name,
                    'subject_code' => $subjectCode !== '' ? $subjectCode : null,
                    'status' => $request->status,
                    'is_duo' => $isDuo,
                ]);

                if ($isDuo) {
                    $this->syncSubjectComponents($subject, $components);
                } else {
                    $subject->components()->delete();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $subject->load('components');

            return response()->json([
                'success' => 'Subject "' . $subject->subject_name . '" updated successfully!',
                'subject' => $subject
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_school_subject($subjectID)
    {
        try {
            // Check permission - Admin has all permissions
            $userType = Session::get('user_type');
            if ($userType !== 'Admin' && !$this->hasPermission('delete_subject')) {
                return response()->json([
                    'error' => 'You do not have permission to delete subjects.'
                ], 403);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subject = SchoolSubject::where('subjectID', $subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'error' => 'Subject not found or does not belong to this school.'
                ], 404);
            }

            // Check if subject is used in any class
            $classSubjectsCount = ClassSubject::where('subjectID', $subjectID)->count();
            if ($classSubjectsCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete subject. It is currently assigned to ' . $classSubjectsCount . ' class(es). Please remove it from all classes first.'
                ], 400);
            }

            $subjectName = $subject->subject_name;
            
            // Delete the subject
            try {
                $subject->delete();
            } catch (\Exception $deleteException) {
                \Log::error('Delete subject database error: ' . $deleteException->getMessage());
                return response()->json([
                    'error' => 'Failed to delete subject from database: ' . $deleteException->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => 'Subject "' . $subjectName . '" deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Delete school subject error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'An error occurred while deleting subject: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_class_subjects_by_subclass($subclassID = null)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $request = request();
            $classID = $request->input('classID');
            $isCoordinatorView = $request->input('coordinator') === 'true';

            // If coordinator view, get subjects for all subclasses in the main class
            if ($isCoordinatorView && $classID) {
                // Get all subclasses for this main class
                $subclassIDs = \App\Models\Subclass::where('classID', $classID)
                    ->where('status', 'Active')
                    ->pluck('subclassID')
                    ->toArray();
                
                if (empty($subclassIDs)) {
                    return response()->json([
                        'success' => true,
                        'subjects' => []
                    ], 200);
                }

                $enrollment = app(StudentEnrollmentService::class);
                $subjects = ClassSubject::whereIn('subclassID', $subclassIDs)
                    ->with(['subject', 'subclass'])
                    ->get()
                    ->map(function($classSubject) use ($enrollment, $schoolID) {
                        // Get election counts
                        $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                        $totalStudents = count($enrollment->studentIDsInSubclass(
                            (int) $classSubject->subclassID,
                            (int) $schoolID,
                            'Active'
                        ));
                        $nonElectors = $totalStudents - $totalElectors;
                        
                        // Get subclass display name
                        $subclassDisplay = 'N/A';
                        if ($classSubject->subclass) {
                            $subclassName = trim($classSubject->subclass->subclass_name ?? '');
                            $className = $classSubject->subclass->class ? $classSubject->subclass->class->class_name : '';
                            $subclassDisplay = empty($subclassName) 
                                ? $className 
                                : $className . ' - ' . $subclassName;
                        }
                        
                        return [
                            'subjectID' => $classSubject->subjectID,
                            'class_subjectID' => $classSubject->class_subjectID,
                            'subclassID' => $classSubject->subclassID,
                            'subclass_display' => $subclassDisplay,
                            'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : null,
                            'student_status' => $classSubject->student_status ?? null,
                            'elected_count' => $totalElectors,
                            'non_elected_count' => $nonElectors,
                            'total_students' => $totalStudents,
                        ];
                    });
            } else {
                // Regular subclass view
                if (!$subclassID) {
                    return response()->json([
                        'error' => 'Subclass ID is required.'
                    ], 400);
                }

                $enrollment = app(StudentEnrollmentService::class);
                $subjects = ClassSubject::where('subclassID', $subclassID)
                    ->with('subject')
                    ->get()
                    ->map(function($classSubject) use ($enrollment, $schoolID) {
                        // Get election counts
                        $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                        $totalStudents = count($enrollment->studentIDsInSubclass(
                            (int) $classSubject->subclassID,
                            (int) $schoolID,
                            'Active'
                        ));
                        $nonElectors = $totalStudents - $totalElectors;
                        
                        return [
                            'subjectID' => $classSubject->subjectID,
                            'class_subjectID' => $classSubject->class_subjectID,
                            'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : null,
                            'student_status' => $classSubject->student_status ?? null,
                            'elected_count' => $totalElectors,
                            'non_elected_count' => $nonElectors,
                            'total_students' => $totalStudents,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Defaults for Add Class Subject: already-assigned IDs + missing combie main subjects.
     */
    public function get_subclass_subject_defaults($subclassID)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subclass = Subclass::with(['combie.subjects.components', 'class'])
                ->where('subclassID', $subclassID)
                ->whereHas('class', function ($query) use ($schoolID) {
                    $query->where('schoolID', $schoolID);
                })
                ->first();

            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $assigned = ClassSubject::where('subclassID', $subclassID)
                ->with('subject')
                ->get();

            $assignedSubjectIDs = $assigned->pluck('subjectID')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $assignedSubjects = $assigned->map(function ($classSubject) {
                return [
                    'subjectID' => (int) $classSubject->subjectID,
                    'class_subjectID' => $classSubject->class_subjectID,
                    'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : null,
                    'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                ];
            })->values();

            $combie = $subclass->combie;
            $combieSubjects = collect();
            if ($combie && $combie->relationLoaded('subjects')) {
                $combieSubjects = $combie->subjects
                    ->filter(function ($subject) {
                        return strcasecmp((string) ($subject->status ?? ''), 'Active') === 0
                            || empty($subject->status);
                    })
                    ->values();
            }

            $missingSubjects = $combieSubjects
                ->reject(fn ($subject) => in_array((int) $subject->subjectID, $assignedSubjectIDs, true))
                ->map(function ($subject) {
                    $components = ($subject->components ?? collect())
                        ->sortBy('sort_order')
                        ->values()
                        ->map(function ($c) {
                            return [
                                'componentID' => (int) $c->componentID,
                                'component_name' => $c->component_name,
                                'max_marks' => (int) $c->max_marks,
                                'sort_order' => (int) $c->sort_order,
                            ];
                        })
                        ->values()
                        ->all();

                    return [
                        'subjectID' => (int) $subject->subjectID,
                        'subject_name' => $subject->subject_name,
                        'subject_code' => $subject->subject_code,
                        'is_duo' => (bool) ($subject->is_duo ?? false) && count($components) > 0,
                        'components' => $components,
                        'sort_order' => (int) ($subject->pivot->sort_order ?? 0),
                    ];
                })
                ->values();

            $alreadyFromCombie = $combieSubjects
                ->filter(fn ($subject) => in_array((int) $subject->subjectID, $assignedSubjectIDs, true))
                ->map(function ($subject) {
                    return [
                        'subjectID' => (int) $subject->subjectID,
                        'subject_name' => $subject->subject_name,
                        'subject_code' => $subject->subject_code,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'subclassID' => (int) $subclass->subclassID,
                'combie' => $combie ? [
                    'combieID' => (int) $combie->combieID,
                    'combie_name' => $combie->combie_name,
                    'combie_code' => $combie->combie_code,
                ] : null,
                'has_combie_subjects' => $combieSubjects->isNotEmpty(),
                'combie_subject_count' => $combieSubjects->count(),
                'assigned_subject_ids' => $assignedSubjectIDs,
                'assigned_subjects' => $assignedSubjects,
                'already_assigned_combie_subjects' => $alreadyFromCombie,
                'missing_subjects' => $missingSubjects,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_class_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('create_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to create class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Validate subclassID
            $validator = Validator::make($request->all(), [
                'subclassID' => 'required|exists:subclasses,subclassID',
                'subjects' => 'required|array|min:1',
                'subjects.*.subjectID' => 'nullable|exists:school_subjects,subjectID',
                'subjects.*.new_subject_name' => 'nullable|string|max:100|required_with:subjects.*.new_subject_code',
                'subjects.*.new_subject_code' => 'nullable|string|max:20',
                'subjects.*.teacherID' => 'nullable|exists:teachers,id',
                'subjects.*.status' => 'required|in:Active,Inactive',
                'subjects.*.student_status' => 'nullable|in:Required,Optional',
                'subjects.*.components' => 'nullable|array',
                'subjects.*.components.*.componentID' => 'required_with:subjects.*.components|exists:school_subject_components,componentID',
                'subjects.*.components.*.teacherID' => 'nullable|exists:teachers,id',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            // Get subclass to get classID
            $subclass = Subclass::find($request->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access to this subclass.'
                ], 403);
            }

            DB::beginTransaction();

            $createdSubjects = [];
            $errors = [];
            $checkIfUserHasApprovalPermission = $this->hasPermission('approve_created_subject');

            foreach ($request->subjects as $index => $subjectData) {
                try {
                    $subjectID = $subjectData['subjectID'] ?? null;
                    $newSubjectName = $subjectData['new_subject_name'] ?? null;
                    $newSubjectCode = $subjectData['new_subject_code'] ?? null;
                    $teacherID = $subjectData['teacherID'] ?? null;
                    $status = $subjectData['status'] ?? 'Active';
                    $studentStatus = $subjectData['student_status'] ?? null;
                    if ($studentStatus === null || $studentStatus === '') {
                        $studentStatus = 'Required';
                    }

                    // Determine if this is a new subject
                    $isNewSubject = ($subjectID === '__new_subject__' || $subjectID === null) && !empty($newSubjectName);

                    // Create new subject if needed
                    if ($isNewSubject) {
                        $newSubjectCode = trim((string) ($newSubjectCode ?? ''));
                        // Reuse only when same subject code already exists in this school.
                        // Subject names may intentionally duplicate (different codes).
                        $existingSubject = null;
                        if ($newSubjectCode !== '') {
                            $existingSubject = SchoolSubject::where('schoolID', $schoolID)
                                ->whereRaw('LOWER(TRIM(subject_code)) = ?', [strtolower($newSubjectCode)])
                                ->first();
                        }

                        if ($existingSubject) {
                            $subjectID = $existingSubject->subjectID;
                        } else {
                            $newSchoolSubject = SchoolSubject::create([
                                'schoolID' => $schoolID,
                                'subject_name' => $newSubjectName,
                                'subject_code' => $newSubjectCode !== '' ? $newSubjectCode : null,
                                'status' => 'Active',
                            ]);
                            $subjectID = $newSchoolSubject->subjectID;
                        }
                    }

                    if (!$subjectID) {
                        $errors[] = "Subject " . ($index + 1) . ": Please select a subject or provide a subject name.";
                        continue;
                    }

                    // Verify subject belongs to this school
                    $subject = SchoolSubject::with('components')->find($subjectID);
                    if (!$subject || $subject->schoolID != $schoolID) {
                        $errors[] = "Subject " . ($index + 1) . ": Subject not found or does not belong to this school.";
                        continue;
                    }

                    $isDuo = (bool) ($subject->is_duo ?? false) && $subject->components->count() > 0;
                    $componentAssignments = is_array($subjectData['components'] ?? null)
                        ? $subjectData['components']
                        : [];

                    // Duo: teachers live on papers only - clear main teacher
                    if ($isDuo) {
                        $teacherID = null;
                        $componentAssignments = $this->normalizeComponentTeacherAssignments(
                            $subject,
                            $componentAssignments,
                            $schoolID,
                            $index,
                            $errors
                        );
                        if ($componentAssignments === null) {
                            continue;
                        }
                    } else {
                        // Handle teacherID for non-duo
                        if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                            $teacherID = null;
                        } else {
                            $teacherID = (int) $teacherID;
                            $teacher = Teacher::find($teacherID);
                            if (!$teacher || $teacher->schoolID != $schoolID) {
                                $errors[] = "Subject " . ($index + 1) . ": Selected teacher not found or does not belong to this school.";
                                continue;
                            }
                        }
                    }

                    // Integrity Constraint: Check if subject already assigned to this subclass only
                    // Note: Removed class-level validation - subject can exist in multiple subclasses within same class
                    // (e.g., in secondary schools, each subclass can have Mathematics, English, etc.)
                    $existingClassSubjectBySubclass = ClassSubject::where('subclassID', $request->subclassID)
                        ->where('subjectID', $subjectID)
                        ->first();

                    if ($existingClassSubjectBySubclass) {
                        $errors[] = "Subject " . ($index + 1) . ": Subject '" . $subject->subject_name . "' is already assigned to this subclass.";
                        continue;
                    }

                    // Integrity Constraint: Check if teacher is already assigned to this subject in this subclass only
                    if (!$isDuo && $teacherID) {
                        $existingTeacherSubject = ClassSubject::where('subclassID', $request->subclassID)
                            ->where('subjectID', $subjectID)
                            ->where('teacherID', $teacherID)
                            ->first();

                        if ($existingTeacherSubject) {
                            $errors[] = "Subject " . ($index + 1) . ": This teacher is already assigned to teach '" . $subject->subject_name . "' in this subclass.";
                            continue;
                        }
                    }

                    // Check if user has approval permission - if yes, use requested status, otherwise set to Inactive
                    $finalStatus = $checkIfUserHasApprovalPermission ? $status : 'Inactive';

                    $classSubject = ClassSubject::create([
                        'classID' => $subclass->classID,
                        'subclassID' => $request->subclassID,
                        'subjectID' => $subjectID,
                        'teacherID' => $teacherID,
                        'status' => $finalStatus,
                        'student_status' => $studentStatus,
                    ]);

                    if ($isDuo) {
                        $this->syncClassSubjectComponentTeachers($classSubject, $subject, $componentAssignments);
                    }

                    $createdSubjects[] = $classSubject;

                } catch (\Exception $e) {
                    $errors[] = "Subject " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            if (!empty($errors) && empty($createdSubjects)) {
                DB::rollBack();
                return response()->json([
                    'error' => implode("\n", $errors)
                ], 400);
            }

            if (!empty($errors)) {
                // Some subjects were created, some failed
                DB::commit();
                $successCount = count($createdSubjects);
                $errorCount = count($errors);
                return response()->json([
                    'success' => "Successfully created {$successCount} subject(s). " . ($errorCount > 0 ? "{$errorCount} subject(s) failed: " . implode("; ", $errors) : ""),
                    'createdSubjects' => $createdSubjects,
                    'warnings' => $errors
                ], 200);
            }

            DB::commit();

            $subjectCount = count($createdSubjects);
            $message = $subjectCount > 1 
                ? "Successfully assigned {$subjectCount} subjects to subclass!" 
                : "Subject assigned to subclass successfully!";

            return response()->json([
                'success' => $message,
                'createdSubjects' => $createdSubjects
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_class_subjects()
    {
        // Check permission
        if (!$this->hasPermission('view_class_subjects')) {
            return response()->json([
                'error' => 'You do not have permission to view class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            // Get all ACTIVE subclasses with their subjects (current year only)
            $subclasses = DB::table('subclasses')
                ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                ->where('classes.schoolID', $schoolID)
                ->where('classes.status', 'Active')
                ->where('subclasses.status', 'Active')
                ->select(
                    'subclasses.subclassID',
                    'subclasses.subclass_name',
                    'subclasses.stream_code',
                    'classes.class_name'
                )
                ->orderBy('classes.class_name')
                ->orderBy('subclasses.subclass_name')
                ->get()
                ->map(function($subclass) use ($schoolID) {
                    $enrollment = app(StudentEnrollmentService::class);
                    // Get subjects for this subclass (show all, not just Active)
                    $subjects = ClassSubject::with([
                            'subject.components',
                            'teacher',
                            'componentTeachers.teacher',
                            'componentTeachers.component',
                        ])
                        ->where('subclassID', $subclass->subclassID)
                        ->get()
                        ->map(function($classSubject) use ($enrollment, $schoolID) {
                            // Get election counts
                            $totalElectors = SubjectElector::where('classSubjectID', $classSubject->class_subjectID)->count();
                            $totalStudents = count($enrollment->studentIDsInSubclass(
                                (int) $classSubject->subclassID,
                                (int) $schoolID,
                                'Active'
                            ));
                            $nonElectors = $totalStudents - $totalElectors;

                            $isDuo = (bool) ($classSubject->subject->is_duo ?? false)
                                && $classSubject->subject
                                && $classSubject->subject->components
                                && $classSubject->subject->components->count() > 0;

                            $paperTeachers = [];
                            if ($isDuo) {
                                $byComp = $classSubject->componentTeachers->keyBy('componentID');
                                $paperTeachers = $classSubject->subject->components
                                    ->sortBy('sort_order')
                                    ->values()
                                    ->map(function ($comp) use ($byComp) {
                                        $assign = $byComp->get($comp->componentID);
                                        $t = $assign ? $assign->teacher : null;
                                        return [
                                            'componentID' => (int) $comp->componentID,
                                            'component_name' => $comp->component_name,
                                            'max_marks' => (int) $comp->max_marks,
                                            'teacherID' => $assign && $assign->teacherID
                                                ? (int) $assign->teacherID
                                                : null,
                                            'teacher_name' => $t
                                                ? trim(($t->first_name ?? '') . ' ' . ($t->last_name ?? ''))
                                                : 'Not Assigned',
                                        ];
                                    })
                                    ->all();
                            }

                            $teacherDisplay = 'Not Assigned';
                            if ($isDuo) {
                                $names = collect($paperTeachers)
                                    ->pluck('teacher_name')
                                    ->filter(fn ($n) => $n && $n !== 'Not Assigned')
                                    ->unique()
                                    ->values();
                                $teacherDisplay = $names->isNotEmpty()
                                    ? $names->implode(', ')
                                    : 'Not Assigned';
                            } elseif ($classSubject->teacher) {
                                $teacherDisplay = $classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name;
                            }
                            
                            return [
                                'class_subjectID' => $classSubject->class_subjectID,
                                'subject_name' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                                'subject_code' => $classSubject->subject ? $classSubject->subject->subject_code : null,
                                'is_duo' => $isDuo,
                                'teacher_name' => $teacherDisplay,
                                'teacher_id' => $classSubject->teacherID,
                                'papers' => $paperTeachers,
                                'status' => $classSubject->status ?? 'Inactive',
                                'student_status' => $classSubject->student_status ?? null,
                                'elected_count' => $totalElectors,
                                'non_elected_count' => $nonElectors,
                                'total_students' => $totalStudents,
                            ];
                        });

                    return [
                        'subclassID' => $subclass->subclassID,
                        'subclass_name' => $subclass->subclass_name,
                        'stream_code' => $subclass->stream_code,
                        'class_name' => $subclass->class_name,
                        'subjects' => $subjects,
                        'subject_count' => $subjects->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'subclasses' => $subclasses
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update_class_subject(Request $request)
    {
        // Check permission
        if (!$this->hasPermission('update_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to update class subjects.'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'class_subjectID' => 'required|exists:class_subjects,class_subjectID',
                'subclassID' => 'required|exists:subclasses,subclassID',
                'teacherID' => 'nullable|exists:teachers,id',
                'components' => 'nullable|array',
                'components.*.componentID' => 'required_with:components|exists:school_subject_components,componentID',
                'components.*.teacherID' => 'nullable|exists:teachers,id',
            ]);

            if ($validator->fails()) {
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $errors[$field] = $messages[0];
                }
                return response()->json(['errors' => $errors], 422);
            }

            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classSubject = ClassSubject::with(['subject.components'])->find($request->class_subjectID);
            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $subclass = Subclass::find($request->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $subject = $classSubject->subject;
            $isDuo = $subject
                && (bool) ($subject->is_duo ?? false)
                && $subject->components
                && $subject->components->count() > 0;

            if ($isDuo) {
                $errors = [];
                $componentAssignments = $this->normalizeComponentTeacherAssignments(
                    $subject,
                    is_array($request->input('components')) ? $request->input('components') : [],
                    $schoolID,
                    0,
                    $errors
                );
                if ($componentAssignments === null) {
                    return response()->json([
                        'error' => !empty($errors) ? implode(' ', $errors) : 'Invalid paper teacher assignments.'
                    ], 422);
                }

                $classSubject->teacherID = null;
                $classSubject->save();
                $this->syncClassSubjectComponentTeachers($classSubject, $subject, $componentAssignments);

                return response()->json([
                    'success' => 'Duo paper teachers updated successfully!'
                ], 200);
            }

            // Non-duo: single main teacher
            $teacherID = $request->input('teacherID');
            if ($teacherID === null || $teacherID === '' || $teacherID === '0' || $teacherID === false) {
                $teacherID = null;
            } else {
                $teacherID = (int) $teacherID;
                $teacher = Teacher::find($teacherID);
                if (!$teacher || $teacher->schoolID != $schoolID) {
                    return response()->json([
                        'error' => 'Selected teacher not found or does not belong to this school.'
                    ], 404);
                }
            }

            // Get old teacher ID before update
            $oldTeacherID = $classSubject->teacherID;
            
            // Update teacher
            $classSubject->teacherID = $teacherID;
            $classSubject->save();

            // Clear any leftover duo paper rows if subject is no longer duo
            ClassSubjectComponentTeacher::where('class_subjectID', $classSubject->class_subjectID)->delete();

            // Update all session timetables for this class_subjectID if teacher changed
            if ($oldTeacherID != $teacherID) {
                try {
                    // Get definition ID
                    $definition = DB::table('session_timetable_definitions')
                        ->where('schoolID', $schoolID)
                        ->first();
                    
                    if ($definition) {
                        // Update all sessions with this class_subjectID
                        $updatedCount = DB::table('class_session_timetables')
                            ->where('class_subjectID', $classSubject->class_subjectID)
                            ->where('definitionID', $definition->definitionID)
                            ->update([
                                'teacherID' => $teacherID,
                                'updated_at' => now()
                            ]);
                        
                        \Log::info('Updated ' . $updatedCount . ' session timetable(s) for class_subjectID: ' . $classSubject->class_subjectID . ' from teacher ' . $oldTeacherID . ' to ' . $teacherID);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating session timetables after teacher change: ' . $e->getMessage());
                    // Don't fail the request if session update fails
                }
            }

            return response()->json([
                'success' => 'Subject teacher updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_class_subject($classSubjectID)
    {
        // Check permission
        if (!$this->hasPermission('delete_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to delete class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classSubject = ClassSubject::find($classSubjectID);
            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $subclass = Subclass::find($classSubject->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $classSubject->delete();

            return response()->json([
                'success' => 'Subject removed from subclass successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_subject(Request $request, $subjectID)
    {
        // Check permission
        if (!$this->hasPermission('activate_subject')) {
            return response()->json([
                'error' => 'You do not have permission to activate subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $subject = SchoolSubject::where('subjectID', $subjectID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    'error' => 'Subject not found or unauthorized access.'
                ], 404);
            }

            // Toggle status
            $newStatus = $subject->status === 'Active' ? 'Inactive' : 'Active';
            $subject->status = $newStatus;
            $subject->save();

            return response()->json([
                'success' => 'Subject "' . $subject->subject_name . '" has been ' . strtolower($newStatus) . 'd successfully!',
                'status' => $newStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate_class_subject(Request $request, $classSubjectID)
    {
        // Check permission
        if (!$this->hasPermission('activate_class_subject')) {
            return response()->json([
                'error' => 'You do not have permission to activate class subjects.'
            ], 403);
        }

        try {
            $schoolID = Session::get('schoolID');

            if (!$schoolID) {
                return response()->json([
                    'error' => 'School ID not found in session.'
                ], 400);
            }

            $classSubject = ClassSubject::find($classSubjectID);

            if (!$classSubject) {
                return response()->json([
                    'error' => 'Class subject not found.'
                ], 404);
            }

            // Verify subclass belongs to this school
            $subclass = Subclass::find($classSubject->subclassID);
            if (!$subclass) {
                return response()->json([
                    'error' => 'Subclass not found.'
                ], 404);
            }

            $class = $subclass->class;
            if ($class->schoolID != $schoolID) {
                return response()->json([
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            // Toggle status
            $newStatus = $classSubject->status === 'Active' ? 'Inactive' : 'Active';
            $classSubject->status = $newStatus;
            $classSubject->save();

            $subjectName = $classSubject->subject ? $classSubject->subject->subject_name : 'Subject';

            return response()->json([
                'success' => 'Class subject "' . $subjectName . '" has been ' . strtolower($newStatus) . 'd successfully!',
                'status' => $newStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_subject_electors($classSubjectID)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID not found in session.'], 400);
            }

            $classSubject = ClassSubject::with('subclass.class')->find($classSubjectID);
            if (!$classSubject) {
                return response()->json(['error' => 'Class subject not found.'], 404);
            }
            
            if (!$classSubject->subclass || !$classSubject->subclass->class) {
                return response()->json(['error' => 'Subclass or class not found for this subject.'], 404);
            }
            
            if ($classSubject->subclass->class->schoolID != $schoolID) {
                return response()->json(['error' => 'Class subject does not belong to this school.'], 403);
            }

            $electors = SubjectElector::where('classSubjectID', $classSubjectID)
                ->get()
                ->map(function($elector) {
                    $student = Student::find($elector->studentID);
                    return [
                        'electorID' => $elector->electorID,
                        'studentID' => $elector->studentID,
                        'student_name' => $student ? $student->first_name . ' ' . $student->last_name : 'N/A',
                    ];
                });

            // Return empty array if no electors (not an error)
            return response()->json([
                'success' => true,
                'electors' => $electors->toArray()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function save_subject_election(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
                'subclassID' => 'required|exists:subclasses,subclassID',
                'selectedStudents' => 'nullable|array',
                'selectedStudents.*' => 'exists:students,studentID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID not found in session.'], 400);
            }

            $classSubject = ClassSubject::with(['subclass.class', 'subject'])->find($request->classSubjectID);
            if (!$classSubject || $classSubject->subclass->class->schoolID != $schoolID) {
                return response()->json(['error' => 'Class subject not found or does not belong to this school.'], 404);
            }

            // Verify subclass matches
            if ($classSubject->subclassID != $request->subclassID) {
                return response()->json(['error' => 'Subclass mismatch.'], 400);
            }

            DB::beginTransaction();

            // Get current electors
            $currentElectors = SubjectElector::where('classSubjectID', $request->classSubjectID)
                ->pluck('studentID')
                ->toArray();

            $selectedStudents = array_values(array_unique(array_map('intval', $request->selectedStudents ?? [])));
            $currentElectors = array_map('intval', $currentElectors);
            $toAdd = array_values(array_diff($selectedStudents, $currentElectors));
            $toRemove = array_values(array_diff($currentElectors, $selectedStudents));

            $enrolledIds = app(StudentEnrollmentService::class)->studentIDsInSubclass(
                (int) $request->subclassID,
                (int) Session::get('schoolID'),
                'Active'
            );

            // Add new electors
            foreach ($toAdd as $studentID) {
                // Verify Active enrollment in this subclass for header year
                if (!in_array((int) $studentID, $enrolledIds, true)) {
                    continue;
                }

                $student = Student::where('studentID', $studentID)->first();

                if ($student) {
                    SubjectElector::create([
                        'studentID' => $studentID,
                        'classSubjectID' => $request->classSubjectID,
                    ]);

                    // TODO: Send SMS to parent
                    // $this->sendElectionSMS($student, $classSubject, 'elected');
                }
            }

            // Remove deselected electors
            foreach ($toRemove as $studentID) {
                $elector = SubjectElector::where('studentID', $studentID)
                    ->where('classSubjectID', $request->classSubjectID)
                    ->first();

                if ($elector) {
                    $student = Student::find($studentID);
                    $elector->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Subject election updated successfully!',
                'added' => count($toAdd),
                'removed' => count($toRemove)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deselect_student(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'classSubjectID' => 'required|exists:class_subjects,class_subjectID',
                'studentID' => 'required|exists:students,studentID',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['error' => 'School ID not found in session.'], 400);
            }

            $classSubject = ClassSubject::with(['subclass.class', 'subject'])->find($request->classSubjectID);
            if (!$classSubject || $classSubject->subclass->class->schoolID != $schoolID) {
                return response()->json(['error' => 'Class subject not found or does not belong to this school.'], 404);
            }

            // Verify student belongs to this subclass
            $student = Student::where('studentID', $request->studentID)
                ->where('subclassID', $classSubject->subclassID)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or does not belong to this subclass.'], 404);
            }

            // Remove from election
            $elector = SubjectElector::where('studentID', $request->studentID)
                ->where('classSubjectID', $request->classSubjectID)
                ->first();

            if ($elector) {
                $elector->delete();

                return response()->json([
                    'success' => 'Student deselected from subject election successfully!'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Student is not currently elected for this subject.'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
