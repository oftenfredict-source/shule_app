<?php

namespace App\Http\Controllers;

use App\Models\HomePackage;
use App\Models\Holiday;
use App\Models\ParentHomePackageNotification;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Services\SelectedAcademicYearService;
use App\Support\MobileApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class HomePackageController extends Controller
{
    private function ayService(): SelectedAcademicYearService
    {
        return app(SelectedAcademicYearService::class);
    }

    private function resolveYearIdForLevel(string $level, ?int $schoolID = null): ?int
    {
        $ctx = $this->ayService()->headerContext($schoolID);

        if ($level === 'A_Level') {
            return $ctx['selected_a_level_academic_yearID']
                ? (int) $ctx['selected_a_level_academic_yearID']
                : null;
        }

        if ($level === 'Primary') {
            return $ctx['selected_academic_yearID']
                ? (int) $ctx['selected_academic_yearID']
                : null;
        }

        return $ctx['selected_o_level_academic_yearID']
            ? (int) $ctx['selected_o_level_academic_yearID']
            : ($ctx['selected_academic_yearID'] ? (int) $ctx['selected_academic_yearID'] : null);
    }

    private function normalizeLevel(?string $level, ?int $schoolID = null): string
    {
        $ctx = $this->ayService()->headerContext($schoolID);
        $mode = $ctx['mode'] ?? 'none';

        if ($mode === 'primary') {
            return 'Primary';
        }

        if ($level === 'A_Level' && ($ctx['has_a_level'] ?? false)) {
            return 'A_Level';
        }

        return 'O_Level';
    }

    private function levelFromClassName(?string $className, ?int $schoolID = null): string
    {
        $ctx = $this->ayService()->headerContext($schoolID);
        if (($ctx['mode'] ?? '') === 'primary') {
            return 'Primary';
        }
        if ($this->ayService()->isALevelClassName($className)) {
            return 'A_Level';
        }

        return 'O_Level';
    }

    /**
     * Class subject owned/taught by this teacher.
     */
    private function ownedClassSubject(int $classSubjectID, int $teacherID): ?ClassSubject
    {
        return ClassSubject::with(['subject', 'class', 'subclass.class'])
            ->where('class_subjectID', $classSubjectID)
            ->where('status', 'Active')
            ->where(function ($q) use ($teacherID) {
                $q->where('teacherID', $teacherID)
                    ->orWhereHas('componentTeachers', function ($cq) use ($teacherID) {
                        $cq->where('teacherID', $teacherID);
                    });
            })
            ->first();
    }

    /**
     * Class-subject IDs relevant to a parent's active children.
     *
     * @return array{class_subject_ids: list<int>, subject_ids: list<int>, subclass_ids: list<int>}
     */
    private function parentChildrenSubjectScope(int $parentID, int $schoolID): array
    {
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get(['studentID', 'subclassID']);

        $subclassIds = $students->pluck('subclassID')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        if (empty($subclassIds)) {
            return ['class_subject_ids' => [], 'subject_ids' => [], 'subclass_ids' => []];
        }

        $classSubjects = ClassSubject::where('status', 'Active')
            ->where(function ($q) use ($subclassIds) {
                $q->whereIn('subclassID', $subclassIds)
                    ->orWhere(function ($q2) use ($subclassIds) {
                        // Whole-class assignments (all streams)
                        $classIds = \App\Models\Subclass::whereIn('subclassID', $subclassIds)
                            ->pluck('classID')
                            ->filter()
                            ->unique()
                            ->all();
                        if (!empty($classIds)) {
                            $q2->whereNull('subclassID')->whereIn('classID', $classIds);
                        } else {
                            $q2->whereRaw('1=0');
                        }
                    });
            })
            ->get(['class_subjectID', 'subjectID']);

        return [
            'class_subject_ids' => $classSubjects->pluck('class_subjectID')->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'subject_ids' => $classSubjects->pluck('subjectID')->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'subclass_ids' => $subclassIds,
        ];
    }

    /**
     * Holidays for teacher Home Package modal (by subject + level → header academic year).
     */
    public function holidaysForLevel(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $classSubjectID = (int) $request->input('class_subjectID', 0);
        $classSubject = $classSubjectID
            ? $this->ownedClassSubject($classSubjectID, $teacherID)
            : null;

        $className = $classSubject
            ? (optional(optional($classSubject->subclass)->class)->class_name
                ?? optional($classSubject->class)->class_name)
            : null;

        $level = $classSubject
            ? $this->normalizeLevel($this->levelFromClassName($className, $schoolID), $schoolID)
            : $this->normalizeLevel($request->input('level'), $schoolID);

        $yearId = $this->resolveYearIdForLevel($level, $schoolID);
        if (!$yearId) {
            return response()->json([
                'success' => true,
                'holidays' => [],
                'message' => 'No academic year selected in the header',
                'academic_yearID' => null,
                'level' => $level,
            ]);
        }

        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function ($q) use ($yearId) {
                $q->where('academic_yearID', $yearId)->orWhereNull('academic_yearID');
            })
            ->orderBy('start_date')
            ->get(['holidayID', 'holiday_name', 'start_date', 'end_date', 'type', 'academic_yearID']);

        $existingQuery = HomePackage::where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->where('academic_yearID', $yearId);
        if ($classSubjectID > 0) {
            $existingQuery->where('class_subjectID', $classSubjectID);
        }
        $existing = $existingQuery->pluck('holidayID')->map(fn ($id) => (int) $id)->all();

        $items = $holidays->map(function ($h) use ($existing) {
            return [
                'holidayID' => (int) $h->holidayID,
                'holiday_name' => $h->holiday_name,
                'start_date' => optional($h->start_date)->format('Y-m-d'),
                'end_date' => optional($h->end_date)->format('Y-m-d'),
                'type' => $h->type,
                'has_package' => in_array((int) $h->holidayID, $existing, true),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'holidays' => $items,
            'academic_yearID' => $yearId,
            'level' => $level,
            'subject_name' => $classSubject->subject->subject_name ?? null,
            'class_subjectID' => $classSubjectID ?: null,
        ]);
    }

    /**
     * List teacher's home packages for a subject (filter by holiday / year from header).
     */
    public function teacherIndex(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $holidayID = (int) $request->input('holidayID', 0);
        $classSubjectID = (int) $request->input('class_subjectID', 0);

        $query = HomePackage::with(['holiday', 'academicYear', 'subject', 'classSubject.subclass.class', 'classSubject.class'])
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID);

        if (!empty($yearIds)) {
            $query->whereIn('academic_yearID', $yearIds);
        }

        if ($holidayID > 0) {
            $query->where('holidayID', $holidayID);
        }

        if ($classSubjectID > 0) {
            $query->where('class_subjectID', $classSubjectID);
        }

        $packages = $query->orderByDesc('created_at')->get()->map(function (HomePackage $p) {
            $classLabel = '-';
            if ($p->classSubject) {
                if ($p->classSubject->subclass && $p->classSubject->subclass->class) {
                    $classLabel = $p->classSubject->subclass->class->class_name.' - '.$p->classSubject->subclass->subclass_name;
                } elseif ($p->classSubject->class) {
                    $classLabel = $p->classSubject->class->class_name.' - All';
                }
            }

            return [
                'home_packageID' => $p->home_packageID,
                'title' => $p->title ?? '',
                'original_filename' => $p->original_filename,
                'level' => $p->level,
                'level_label' => $p->levelLabel(),
                'subject_name' => $p->subject->subject_name ?? '-',
                'class_label' => $classLabel,
                'class_subjectID' => $p->class_subjectID,
                'holidayID' => $p->holidayID,
                'holiday_name' => $p->holiday->holiday_name ?? '-',
                'holiday_dates' => $p->holiday
                    ? (optional($p->holiday->start_date)->format('d/m/Y') . ' - ' . optional($p->holiday->end_date)->format('d/m/Y'))
                    : '-',
                'academic_yearID' => $p->academic_yearID,
                'year_label' => $p->academicYear->year_name
                    ?? ($p->academicYear->year ?? '-'),
                'mime_type' => $p->mime_type,
                'created_at' => optional($p->created_at)->format('d/m/Y H:i'),
                'view_url' => route('home_packages.view', $p->home_packageID),
                'download_url' => route('home_packages.download', $p->home_packageID),
            ];
        });

        return response()->json(['success' => true, 'packages' => $packages]);
    }

    public function store(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'holidayID' => 'required|integer',
            'class_subjectID' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $classSubject = $this->ownedClassSubject((int) $request->class_subjectID, $teacherID);
        if (!$classSubject) {
            return response()->json(['error' => 'You do not have permission for this subject'], 403);
        }

        $className = optional(optional($classSubject->subclass)->class)->class_name
            ?? optional($classSubject->class)->class_name;
        $level = $this->normalizeLevel($this->levelFromClassName($className, $schoolID), $schoolID);
        $yearId = $this->resolveYearIdForLevel($level, $schoolID);
        if (!$yearId) {
            return response()->json(['error' => 'Please select an academic year in the header first'], 422);
        }

        $holiday = Holiday::where('schoolID', $schoolID)
            ->where('holidayID', (int) $request->holidayID)
            ->where(function ($q) use ($yearId) {
                $q->where('academic_yearID', $yearId)->orWhereNull('academic_yearID');
            })
            ->first();

        if (!$holiday) {
            return response()->json(['error' => 'Holiday not found for this academic year'], 404);
        }

        $exists = HomePackage::where('teacherID', $teacherID)
            ->where('holidayID', $holiday->holidayID)
            ->where('class_subjectID', $classSubject->class_subjectID)
            ->exists();
        if ($exists) {
            return response()->json([
                'error' => 'You already uploaded a home package for this subject and holiday. Delete or edit to change it.',
            ], 422);
        }

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $fileSize = $file->getSize();
            $fileName = time().'_'.$originalName;
            $uploadPath = $this->resolveHomePackageUploadPath();
            $file->move($uploadPath, $fileName);
            $relativePath = 'home_packages/'.$fileName;

            $package = HomePackage::create([
                'schoolID' => $schoolID,
                'academic_yearID' => $yearId,
                'holidayID' => $holiday->holidayID,
                'subjectID' => $classSubject->subjectID,
                'class_subjectID' => $classSubject->class_subjectID,
                'teacherID' => $teacherID,
                'level' => $level,
                'title' => $request->input('title') ?: pathinfo($originalName, PATHINFO_FILENAME),
                'file_path' => $relativePath,
                'original_filename' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);

            if (!$holiday->academic_yearID) {
                $holiday->academic_yearID = $yearId;
                $holiday->save();
            }

            $this->notifyParents($package);

            return response()->json([
                'success' => true,
                'message' => 'Home package saved successfully',
                'package' => $package,
            ]);
        } catch (\Exception $e) {
            Log::error('Home package upload failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to upload file. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, $homePackageID)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $package = HomePackage::where('home_packageID', $homePackageID)
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->first();

        if (!$package) {
            return response()->json(['error' => 'Home package not found'], 404);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'holidayID' => 'nullable|integer',
            'class_subjectID' => 'nullable|integer',
        ]);

        try {
            if ($request->filled('title')) {
                $package->title = $request->input('title');
            }

            $classSubjectID = (int) ($request->input('class_subjectID') ?: $package->class_subjectID);
            $classSubject = $this->ownedClassSubject($classSubjectID, $teacherID);
            if (!$classSubject) {
                return response()->json(['error' => 'You do not have permission for this subject'], 403);
            }

            $className = optional(optional($classSubject->subclass)->class)->class_name
                ?? optional($classSubject->class)->class_name;
            $level = $this->normalizeLevel($this->levelFromClassName($className, $schoolID), $schoolID);
            $yearId = $this->resolveYearIdForLevel($level, $schoolID) ?: (int) $package->academic_yearID;
            $holidayID = (int) ($request->input('holidayID') ?: $package->holidayID);

            $holiday = Holiday::where('schoolID', $schoolID)
                ->where('holidayID', $holidayID)
                ->where(function ($q) use ($yearId) {
                    $q->where('academic_yearID', $yearId)->orWhereNull('academic_yearID');
                })
                ->first();

            if (!$holiday) {
                return response()->json(['error' => 'Holiday not found'], 404);
            }

            $dup = HomePackage::where('teacherID', $teacherID)
                ->where('holidayID', $holiday->holidayID)
                ->where('class_subjectID', $classSubject->class_subjectID)
                ->where('home_packageID', '!=', $package->home_packageID)
                ->exists();
            if ($dup) {
                return response()->json(['error' => 'A home package already exists for this subject and holiday'], 422);
            }

            $package->holidayID = $holiday->holidayID;
            $package->level = $level;
            $package->academic_yearID = $yearId;
            $package->subjectID = $classSubject->subjectID;
            $package->class_subjectID = $classSubject->class_subjectID;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $fileSize = $file->getSize();
                $fileName = time().'_'.$originalName;
                $uploadPath = $this->resolveHomePackageUploadPath();

                if ($package->file_path) {
                    $this->deleteHomePackageFile($package->file_path, $uploadPath);
                }

                $file->move($uploadPath, $fileName);
                $package->file_path = 'home_packages/'.$fileName;
                $package->original_filename = $originalName;
                $package->mime_type = $mimeType;
                $package->file_size = $fileSize;
                if (!$request->filled('title')) {
                    $package->title = pathinfo($originalName, PATHINFO_FILENAME);
                }
            }

            $package->save();

            return response()->json([
                'success' => true,
                'message' => 'Home package updated successfully',
                'package' => $package,
            ]);
        } catch (\Exception $e) {
            Log::error('Home package update failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to update. Please try again.',
            ], 500);
        }
    }

    public function destroy($homePackageID)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $package = HomePackage::where('home_packageID', $homePackageID)
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->first();

        if (!$package) {
            return response()->json(['error' => 'Home package not found'], 404);
        }

        ParentHomePackageNotification::where('home_packageID', $package->home_packageID)->delete();

        if ($package->file_path) {
            $this->deleteHomePackageFile(
                $package->file_path,
                $this->resolveHomePackageUploadPath()
            );
        }

        $package->delete();

        return response()->json(['success' => true, 'message' => 'Home package deleted successfully']);
    }

    public function viewFile($homePackageID)
    {
        return $this->serveHomePackageFile((int) $homePackageID, true);
    }

    public function downloadFile($homePackageID)
    {
        return $this->serveHomePackageFile((int) $homePackageID, false);
    }

    /**
     * Serve home package file (inline view or forced download) - same path resolution as exam papers.
     */
    private function serveHomePackageFile(int $homePackageID, bool $inline)
    {
        try {
            $package = $this->authorizePackageAccess($homePackageID);
            if (!$package || !$package->file_path) {
                abort(404, 'File not found');
            }

            $foundPath = $this->resolveExistingHomePackagePath($package->file_path);
            if (!$foundPath) {
                abort(404, 'File not found');
            }

            $fileName = $package->original_filename ?: basename($package->file_path);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $foundPath) ?: ($package->mime_type ?: 'application/octet-stream');
            finfo_close($finfo);

            if ($inline || $mimeType === 'application/pdf') {
                return response()->file($foundPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="'.$fileName.'"',
                ]);
            }

            return response()->download($foundPath, $fileName);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error serving home package: '.$e->getMessage());
            abort(404, 'File not found');
        }
    }

    /**
     * Parent page: holidays that have uploaded home packages for children's subjects.
     */
    public function parentIndex()
    {
        $userType = strtolower((string) Session::get('user_type'));
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');

        if ($userType !== 'parent' || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $scope = $this->parentChildrenSubjectScope($parentID, $schoolID);
        $classSubjectIds = $scope['class_subject_ids'];

        $holidays = collect();
        if (!empty($classSubjectIds)) {
            $query = HomePackage::with('holiday')
                ->where('schoolID', $schoolID)
                ->whereIn('class_subjectID', $classSubjectIds);

            if (!empty($yearIds)) {
                $query->whereIn('academic_yearID', $yearIds);
            }

            $holidays = $query->get()
                ->pluck('holiday')
                ->filter()
                ->unique('holidayID')
                ->sortBy('start_date')
                ->values();
        }

        if (request()->attributes->get('mobile_api')) {
            return response()->json([
                'success' => true,
                'message' => 'Home packages retrieved successfully',
                'data' => [
                    'holidays' => $holidays->map(function ($holiday) {
                        return [
                            'holidayID' => $holiday->holidayID,
                            'holiday_name' => $holiday->holiday_name,
                            'start_date' => optional($holiday->start_date)->format('Y-m-d'),
                            'end_date' => optional($holiday->end_date)->format('Y-m-d'),
                        ];
                    })->values(),
                ],
            ]);
        }

        return view('Parents.home_packages', compact('holidays', 'yearIds'));
    }

    /**
     * Parent AJAX: packages for a holiday across all of the child's subjects.
     */
    public function parentHolidayPackages(Request $request, $holidayID)
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $scope = $this->parentChildrenSubjectScope($parentID, $schoolID);
        $classSubjectIds = $scope['class_subject_ids'];

        $holiday = Holiday::where('schoolID', $schoolID)
            ->where('holidayID', (int) $holidayID)
            ->first();

        if (!$holiday) {
            return response()->json(['error' => 'Holiday not found'], 404);
        }

        if (empty($classSubjectIds)) {
            return response()->json([
                'success' => true,
                'holiday' => [
                    'holidayID' => $holiday->holidayID,
                    'holiday_name' => $holiday->holiday_name,
                    'dates' => optional($holiday->start_date)->format('d/m/Y').' - '.optional($holiday->end_date)->format('d/m/Y'),
                ],
                'packages' => [],
            ]);
        }

        $query = HomePackage::with(['subject', 'teacher', 'classSubject.subclass.class', 'classSubject.class'])
            ->where('schoolID', $schoolID)
            ->where('holidayID', $holiday->holidayID)
            ->whereIn('class_subjectID', $classSubjectIds);

        if (!empty($yearIds)) {
            $query->whereIn('academic_yearID', $yearIds);
        }

        $packages = $query->orderBy('subjectID')->orderByDesc('created_at')->get()->map(function (HomePackage $p) {
            $classLabel = '-';
            if ($p->classSubject) {
                if ($p->classSubject->subclass && $p->classSubject->subclass->class) {
                    $classLabel = $p->classSubject->subclass->class->class_name.' - '.$p->classSubject->subclass->subclass_name;
                } elseif ($p->classSubject->class) {
                    $classLabel = $p->classSubject->class->class_name;
                }
            }

            return [
                'home_packageID' => $p->home_packageID,
                'title' => $p->title ?: $p->original_filename,
                'original_filename' => $p->original_filename,
                'subject_name' => $p->subject->subject_name ?? 'Subject',
                'subject_code' => $p->subject->subject_code ?? null,
                'class_label' => $classLabel,
                'teacher_name' => $p->teacher
                    ? trim(($p->teacher->first_name ?? '').' '.($p->teacher->last_name ?? ''))
                    : '-',
                'view_url' => MobileApi::packageViewUrl((int) $p->home_packageID),
                'download_url' => MobileApi::packageDownloadUrl((int) $p->home_packageID),
            ];
        });

        return response()->json([
            'success' => true,
            'holiday' => [
                'holidayID' => $holiday->holidayID,
                'holiday_name' => $holiday->holiday_name,
                'dates' => optional($holiday->start_date)->format('d/m/Y').' - '.optional($holiday->end_date)->format('d/m/Y'),
            ],
            'packages' => $packages,
        ]);
    }

    public function parentNotifications()
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notifications = ParentHomePackageNotification::with([
            'homePackage.holiday',
            'homePackage.teacher',
            'homePackage.subject',
            'homePackage.academicYear',
        ])
            ->where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(function (ParentHomePackageNotification $n) {
                $p = $n->homePackage;
                $subject = $p->subject->subject_name ?? 'somo';
                $holiday = $p->holiday->holiday_name ?? '';
                $mime = strtolower((string) ($p->mime_type ?? ''));
                $filename = strtolower((string) ($p->original_filename ?? ''));
                $isPdf = str_contains($mime, 'pdf') || str_ends_with($filename, '.pdf');
                $isDoc = str_contains($mime, 'word')
                    || str_contains($mime, 'msword')
                    || str_contains($mime, 'officedocument')
                    || str_ends_with($filename, '.doc')
                    || str_ends_with($filename, '.docx');
                $fileKind = $isPdf ? 'pdf' : ($isDoc ? 'doc' : 'file');

                return [
                    'id' => $n->id,
                    'is_viewed' => (bool) $n->is_viewed,
                    'created_at' => optional($n->created_at)->diffForHumans(),
                    'message' => 'Kifurushi kipya cha masomo ya nyumbani'
                        . ($subject ? ' - '.$subject : '')
                        . ($holiday ? ' ('.$holiday.')' : ''),
                    'holiday_name' => $holiday ?: '-',
                    'subject_name' => $subject,
                    'level_label' => $p ? $p->levelLabel() : '-',
                    'teacher_name' => $p && $p->teacher
                        ? trim(($p->teacher->first_name ?? '') . ' ' . ($p->teacher->last_name ?? ''))
                        : '-',
                    'home_packageID' => $p->home_packageID ?? null,
                    'original_filename' => $p->original_filename ?? null,
                    'mime_type' => $p->mime_type ?? null,
                    'file_kind' => $fileKind,
                    'view_url' => $p ? route('home_packages.view', $p->home_packageID) : null,
                    'download_url' => $p ? route('home_packages.download', $p->home_packageID) : null,
                ];
            });

        $unread = ParentHomePackageNotification::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('is_viewed', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread' => $unread,
            'notifications' => $notifications,
        ]);
    }

    public function markNotificationRead(Request $request, $id = null)
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->boolean('all') || $id === 'all') {
            ParentHomePackageNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->where('is_viewed', false)
                ->update(['is_viewed' => true]);

            return response()->json(['success' => true, 'message' => 'Arifa zote zimesomwa']);
        }

        $n = ParentHomePackageNotification::where('id', $id)
            ->where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->first();

        if (!$n) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $n->is_viewed = true;
        $n->save();

        return response()->json(['success' => true]);
    }

    private function authorizePackageAccess(int $homePackageID): ?HomePackage
    {
        $schoolID = (int) Session::get('schoolID');
        $userType = strtolower((string) Session::get('user_type'));
        $teacherID = (int) Session::get('teacherID');
        $parentID = (int) Session::get('parentID');

        $package = HomePackage::where('home_packageID', $homePackageID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$package) {
            return null;
        }

        if (in_array($userType, ['admin', 'teacher', 'staff'], true)) {
            if ($userType === 'teacher' && (int) $package->teacherID !== $teacherID) {
                // Teachers may view own packages; others in school for admin/staff ok.
                // Allow school teachers to view for preview if needed? User said teacher manages own.
                // Keep own-only for teacher edit, but allow view of own only:
                return $package->teacherID == $teacherID ? $package : null;
            }
            return $package;
        }

        if ($userType === 'parent' && $parentID) {
            $scope = $this->parentChildrenSubjectScope($parentID, $schoolID);
            if (empty($scope['class_subject_ids'])) {
                return null;
            }
            if ($package->class_subjectID && in_array((int) $package->class_subjectID, $scope['class_subject_ids'], true)) {
                return $package;
            }
            // Legacy packages without class_subjectID: allow by subject if child takes it
            if (!$package->class_subjectID && $package->subjectID
                && in_array((int) $package->subjectID, $scope['subject_ids'], true)) {
                return $package;
            }

            return null;
        }

        return null;
    }

    private function notifyParents(HomePackage $package): void
    {
        $package->loadMissing(['classSubject']);
        $cs = $package->classSubject;
        if (!$cs) {
            return;
        }

        $studentQuery = Student::where('schoolID', $package->schoolID)
            ->where('status', 'Active')
            ->whereNotNull('parentID');

        if ($cs->subclassID) {
            $studentQuery->where('subclassID', $cs->subclassID);
        } elseif ($cs->classID) {
            $subclassIds = \App\Models\Subclass::where('classID', $cs->classID)->pluck('subclassID');
            $studentQuery->whereIn('subclassID', $subclassIds);
        } else {
            return;
        }

        $parentIds = $studentQuery->pluck('parentID')->unique()->filter()->values();

        foreach ($parentIds as $parentID) {
            ParentHomePackageNotification::updateOrCreate(
                [
                    'parentID' => (int) $parentID,
                    'home_packageID' => $package->home_packageID,
                ],
                [
                    'schoolID' => $package->schoolID,
                    'is_viewed' => false,
                ]
            );
        }
    }

    /**
     * Same strategy as exam papers: works on local, cPanel main domain, and subdomain.
     */
    private function resolveHomePackageUploadPath(): string
    {
        $folder = 'home_packages';
        $basePath = base_path();
        $parentDir = dirname($basePath);
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : null;

        $candidates = array_values(array_unique(array_filter([
            $scriptDir ? $scriptDir.'/'.$folder : null,
            isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/'.$folder : null,
            public_path($folder),
            $parentDir.'/public_html/'.$folder,
        ])));

        foreach ($candidates as $candidate) {
            if ($this->ensureWritableUploadDirectory($candidate)) {
                return $candidate;
            }
        }

        $fallback = public_path($folder);
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0777, true);
        }
        $this->ensureWritableUploadDirectory($fallback);

        return $fallback;
    }

    private function ensureWritableUploadDirectory(string $dir): bool
    {
        if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
            return false;
        }

        $probe = $dir.'/.upload_probe_'.uniqid('', true);
        if (@file_put_contents($probe, '1') !== false) {
            @unlink($probe);

            return true;
        }

        $referenceDir = dirname($dir).'/userImages';
        if (is_dir($referenceDir)) {
            $refPerms = fileperms($referenceDir) & 0777;
            if ($refPerms > 0) {
                @chmod($dir, $refPerms);
            } else {
                @chmod($dir, 0777);
            }

            if (@file_put_contents($probe, '1') !== false) {
                @unlink($probe);

                return true;
            }
        }

        @chmod($dir, 0777);

        if (@file_put_contents($probe, '1') !== false) {
            @unlink($probe);

            return true;
        }

        return false;
    }

    /**
     * Find stored file across local / cPanel / storage-link locations (exam paper style).
     */
    private function resolveExistingHomePackagePath(string $relativePath): ?string
    {
        $scriptPath = isset($_SERVER['SCRIPT_FILENAME'])
            ? dirname($_SERVER['SCRIPT_FILENAME']).'/'.$relativePath
            : null;

        $basePath = base_path();
        $parentDir = dirname($basePath);

        $pathsToCheck = [
            $scriptPath,
            public_path($relativePath),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'].'/'.$relativePath : null,
            Storage::disk('public')->path($relativePath),
            $parentDir.'/public_html/'.$relativePath,
        ];

        foreach ($pathsToCheck as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function deleteHomePackageFile(string $relativePath, string $uploadPath): void
    {
        $oldFilePath = $uploadPath.'/'.basename($relativePath);
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }

        $resolved = $this->resolveExistingHomePackagePath($relativePath);
        if ($resolved && file_exists($resolved)) {
            @unlink($resolved);
        }

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
