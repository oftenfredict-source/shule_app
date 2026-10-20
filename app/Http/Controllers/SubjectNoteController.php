<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\ParentSubjectNoteNotification;
use App\Models\Student;
use App\Models\SubjectNote;
use App\Services\SelectedAcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SubjectNoteController extends Controller
{
    private function ayService(): SelectedAcademicYearService
    {
        return app(SelectedAcademicYearService::class);
    }

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

    private function resolveYearIdForClassSubject(?ClassSubject $classSubject, int $schoolID): ?int
    {
        $ctx = $this->ayService()->headerContext($schoolID);
        $className = $classSubject
            ? (optional(optional($classSubject->subclass)->class)->class_name
                ?? optional($classSubject->class)->class_name)
            : null;

        if (($ctx['mode'] ?? '') === 'primary') {
            return $ctx['selected_academic_yearID'] ? (int) $ctx['selected_academic_yearID'] : null;
        }
        if ($className && $this->ayService()->isALevelClassName($className) && ($ctx['has_a_level'] ?? false)) {
            return $ctx['selected_a_level_academic_yearID']
                ? (int) $ctx['selected_a_level_academic_yearID']
                : null;
        }

        return $ctx['selected_o_level_academic_yearID']
            ? (int) $ctx['selected_o_level_academic_yearID']
            : ($ctx['selected_academic_yearID'] ? (int) $ctx['selected_academic_yearID'] : null);
    }

    private function levelFromClassSubject(?ClassSubject $classSubject, int $schoolID): string
    {
        $ctx = $this->ayService()->headerContext($schoolID);
        if (($ctx['mode'] ?? '') === 'primary') {
            return 'Primary';
        }
        $className = $classSubject
            ? (optional(optional($classSubject->subclass)->class)->class_name
                ?? optional($classSubject->class)->class_name)
            : null;
        if ($className && $this->ayService()->isALevelClassName($className)) {
            return 'A_Level';
        }

        return 'O_Level';
    }

    private function classSubjectsForStudent(Student $student): array
    {
        $subclassID = (int) ($student->subclassID ?? 0);
        if ($subclassID < 1) {
            return [];
        }
        if (!$student->relationLoaded('subclass')) {
            $student->load('subclass');
        }
        $classID = (int) (optional($student->subclass)->classID ?? 0);

        return ClassSubject::where('status', 'Active')
            ->where(function ($q) use ($subclassID, $classID) {
                $q->where('subclassID', $subclassID);
                if ($classID > 0) {
                    $q->orWhere(function ($q2) use ($classID) {
                        $q2->whereNull('subclassID')->where('classID', $classID);
                    });
                }
            })
            ->pluck('class_subjectID')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function teacherIndex(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $classSubjectID = (int) $request->input('class_subjectID', 0);
        $classSubject = $classSubjectID ? $this->ownedClassSubject($classSubjectID, $teacherID) : null;
        if (!$classSubject) {
            return response()->json(['error' => 'You do not have permission for this subject'], 403);
        }

        $yearId = $this->resolveYearIdForClassSubject($classSubject, $schoolID);
        if (!Schema::hasTable('subject_notes')) {
            return response()->json([
                'success' => true,
                'notes' => [],
                'subject_name' => $classSubject->subject->subject_name ?? 'Somo',
            ]);
        }
        $query = SubjectNote::with(['subject', 'teacher'])
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->where('class_subjectID', $classSubject->class_subjectID);
        if ($yearId) {
            $query->where('academic_yearID', $yearId);
        }

        $notes = $query->orderByDesc('created_at')->get()->map(function (SubjectNote $n) {
            return $this->notePayload($n, true);
        });

        return response()->json([
            'success' => true,
            'notes' => $notes,
            'subject_name' => $classSubject->subject->subject_name ?? 'Somo',
        ]);
    }

    public function store(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $multiFiles = $request->file('files');
        if ($multiFiles instanceof \Illuminate\Http\UploadedFile) {
            $multiFiles = [$multiFiles];
        }
        $isMulti = is_array($multiFiles) && count($multiFiles) > 0;

        if ($isMulti) {
            $request->validate([
                'class_subjectID' => 'required|integer',
                'titles' => 'required|array|min:1',
                'titles.*' => 'required|string|max:255',
                'files' => 'required',
                'files.*' => 'required|file|mimes:pdf,doc,docx|max:51200',
            ]);
        } else {
            $request->validate([
                'class_subjectID' => 'required|integer',
                'title' => 'required|string|max:255',
                'file' => 'required|file|mimes:pdf,doc,docx|max:51200',
            ]);
        }

        $classSubject = $this->ownedClassSubject((int) $request->class_subjectID, $teacherID);
        if (!$classSubject) {
            return response()->json(['error' => 'You do not have permission for this subject'], 403);
        }

        $yearId = $this->resolveYearIdForClassSubject($classSubject, $schoolID);
        if (!$yearId) {
            return response()->json(['error' => 'Please select an academic year in the header first'], 422);
        }

        $items = [];
        if ($isMulti) {
            $titles = array_values((array) $request->input('titles', []));
            foreach (array_values($multiFiles) as $i => $file) {
                if (!$file) {
                    continue;
                }
                $items[] = [
                    'title' => trim((string) ($titles[$i] ?? '')),
                    'file' => $file,
                ];
            }
        } else {
            $items[] = [
                'title' => trim((string) $request->input('title')),
                'file' => $request->file('file'),
            ];
        }

        if (empty($items)) {
            return response()->json(['error' => 'Please upload at least one PDF or Word file'], 422);
        }

        try {
            $saved = [];
            $uploadPath = $this->resolveUploadPath();
            foreach ($items as $index => $item) {
                $title = $item['title'];
                if ($title === '') {
                    return response()->json(['error' => 'Each note needs a title / topic range'], 422);
                }
                $saved[] = $this->createNoteFromUpload(
                    $item['file'],
                    $title,
                    $classSubject,
                    $schoolID,
                    $teacherID,
                    $yearId,
                    $uploadPath,
                    $index
                );
            }

            $count = count($saved);
            $message = $count === 1
                ? 'Note saved successfully'
                : $count.' notes saved successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'notes' => array_map(fn (SubjectNote $n) => $this->notePayload($n, true), $saved),
                'note' => $this->notePayload($saved[0], true),
            ]);
        } catch (\Throwable $e) {
            Log::error('Subject note upload failed: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['error' => 'Failed to upload file. Please try again.'], 500);
        }
    }

    public function update(Request $request, $noteID)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $note = SubjectNote::where('noteID', $noteID)
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->first();
        if (!$note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:51200',
        ]);

        try {
            $note->title = trim((string) $request->input('title'));
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $fileSize = $file->getSize();
                $fileName = time().'_'.$originalName;
                $uploadPath = $this->resolveUploadPath();
                $this->deleteNoteFile($note->file_path, $uploadPath);
                $file->move($uploadPath, $fileName);
                $note->file_path = 'teacher_notes/'.$fileName;
                $note->original_filename = $originalName;
                $note->mime_type = $mimeType;
                $note->file_size = $fileSize;
            }
            $note->save();

            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully',
                'note' => $this->notePayload($note, true),
            ]);
        } catch (\Exception $e) {
            Log::error('Subject note update failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to update. Please try again.'], 500);
        }
    }

    public function destroy($noteID)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = (int) Session::get('teacherID');
        if (!$schoolID || !$teacherID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $note = SubjectNote::where('noteID', $noteID)
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->first();
        if (!$note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        ParentSubjectNoteNotification::where('noteID', $note->noteID)->delete();
        if ($note->file_path) {
            $this->deleteNoteFile($note->file_path, $this->resolveUploadPath());
        }
        $note->delete();

        return response()->json(['success' => true, 'message' => 'Note deleted successfully']);
    }

    public function viewFile($noteID)
    {
        return $this->serveNoteFile((int) $noteID, true);
    }

    public function downloadFile($noteID)
    {
        return $this->serveNoteFile((int) $noteID, false);
    }

    public function parentIndex()
    {
        $userType = strtolower((string) Session::get('user_type'));
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if ($userType !== 'parent' || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $notesReady = Schema::hasTable('subject_notes') && Schema::hasTable('parent_subject_note_notifications');
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['subclass.class'])
            ->orderBy('first_name')
            ->get();

        $children = $students->map(function (Student $student) use ($schoolID, $parentID, $yearIds, $notesReady) {
            $csIds = $this->classSubjectsForStudent($student);
            $noteCount = 0;
            $unread = 0;
            if ($notesReady) {
                $notesQuery = SubjectNote::where('schoolID', $schoolID);
                if (!empty($csIds)) {
                    $notesQuery->whereIn('class_subjectID', $csIds);
                } else {
                    $notesQuery->whereRaw('1=0');
                }
                if (!empty($yearIds)) {
                    $notesQuery->whereIn('academic_yearID', $yearIds);
                }
                $noteCount = $notesQuery->count();
                $unread = ParentSubjectNoteNotification::where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->where('studentID', $student->studentID)
                    ->where('is_viewed', false)
                    ->count();
            }

            return [
                'studentID' => $student->studentID,
                'full_name' => trim($student->first_name.' '.($student->middle_name ? $student->middle_name.' ' : '').$student->last_name),
                'admission_number' => $student->admission_number,
                'class_label' => $student->subclass && $student->subclass->class
                    ? trim($student->subclass->class->class_name.' '.($student->subclass->subclass_name ?? ''))
                    : 'N/A',
                'photo' => $student->photo ? asset('userImages/'.$student->photo) : null,
                'gender' => $student->gender,
                'notes_count' => $noteCount,
                'unread_count' => $unread,
            ];
        });

        return view('Parents.subject_notes', compact('children', 'locale'));
    }

    public function parentChildSubjects(Request $request, $studentID)
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::where('studentID', $studentID)
            ->where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['subclass.class'])
            ->first();
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $subclassID = (int) ($student->subclassID ?? 0);
        $classID = (int) (optional($student->subclass)->classID ?? 0);

        $classSubjects = ClassSubject::with(['subject', 'teacher'])
            ->where('status', 'Active')
            ->where(function ($q) use ($subclassID, $classID) {
                if ($subclassID > 0) {
                    $q->where('subclassID', $subclassID);
                }
                if ($classID > 0) {
                    $q->orWhere(function ($q2) use ($classID) {
                        $q2->whereNull('subclassID')->where('classID', $classID);
                    });
                }
            })
            ->get();

        $csIds = $classSubjects->pluck('class_subjectID')->map(fn ($id) => (int) $id)->all();
        $noteCounts = [];
        $unreadBySubject = [];
        if (!empty($csIds) && Schema::hasTable('subject_notes')) {
            $q = SubjectNote::where('schoolID', $schoolID)->whereIn('class_subjectID', $csIds);
            if (!empty($yearIds)) {
                $q->whereIn('academic_yearID', $yearIds);
            }
            $noteCounts = $q->selectRaw('class_subjectID, COUNT(*) as total')
                ->groupBy('class_subjectID')
                ->pluck('total', 'class_subjectID')
                ->all();
        }

        if (!empty($csIds) && Schema::hasTable('parent_subject_note_notifications') && Schema::hasTable('subject_notes')) {
            $unreadBySubject = ParentSubjectNoteNotification::query()
                ->join('subject_notes', 'subject_notes.noteID', '=', 'parent_subject_note_notifications.noteID')
                ->where('parent_subject_note_notifications.schoolID', $schoolID)
                ->where('parent_subject_note_notifications.parentID', $parentID)
                ->where('parent_subject_note_notifications.studentID', $student->studentID)
                ->where('parent_subject_note_notifications.is_viewed', false)
                ->whereIn('subject_notes.class_subjectID', $csIds)
                ->selectRaw('subject_notes.class_subjectID, COUNT(*) as total')
                ->groupBy('subject_notes.class_subjectID')
                ->pluck('total', 'class_subjectID')
                ->all();
        }

        $subjects = $classSubjects->map(function (ClassSubject $cs) use ($noteCounts, $unreadBySubject) {
            $id = (int) $cs->class_subjectID;

            return [
                'class_subjectID' => $id,
                'subject_name' => $cs->subject->subject_name ?? 'Somo',
                'subject_code' => $cs->subject->subject_code ?? null,
                'teacher_name' => $cs->teacher
                    ? trim(($cs->teacher->first_name ?? '').' '.($cs->teacher->last_name ?? ''))
                    : '-',
                'notes_count' => (int) ($noteCounts[$id] ?? 0),
                'unread_count' => (int) ($unreadBySubject[$id] ?? 0),
            ];
        })->sortBy(function ($row) {
            return mb_strtolower((string) ($row['subject_name'] ?? ''));
        }, SORT_NATURAL)->values();

        return response()->json([
            'success' => true,
            'student' => [
                'studentID' => $student->studentID,
                'full_name' => trim($student->first_name.' '.$student->last_name),
                'class_label' => $student->subclass && $student->subclass->class
                    ? trim($student->subclass->class->class_name.' '.($student->subclass->subclass_name ?? ''))
                    : 'N/A',
            ],
            'subjects' => $subjects,
        ]);
    }

    public function parentSubjectNotes(Request $request, $studentID, $classSubjectID)
    {
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if (!$schoolID || !$parentID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::where('studentID', $studentID)
            ->where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with('subclass')
            ->first();
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $allowed = $this->classSubjectsForStudent($student);
        if (!in_array((int) $classSubjectID, $allowed, true)) {
            return response()->json(['error' => 'Subject not found for this student'], 404);
        }

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        if (!Schema::hasTable('subject_notes')) {
            $classSubject = ClassSubject::with('subject')->where('class_subjectID', (int) $classSubjectID)->first();

            return response()->json([
                'success' => true,
                'subject_name' => $classSubject->subject->subject_name ?? 'Somo',
                'notes' => [],
            ]);
        }
        $query = SubjectNote::with(['subject', 'teacher'])
            ->where('schoolID', $schoolID)
            ->where('class_subjectID', (int) $classSubjectID);
        if (!empty($yearIds)) {
            $query->whereIn('academic_yearID', $yearIds);
        }

        $notes = $query->orderByDesc('created_at')->get()->map(function (SubjectNote $n) {
            return $this->notePayload($n, false);
        });

        if (Schema::hasTable('parent_subject_note_notifications')) {
            ParentSubjectNoteNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->where('studentID', $student->studentID)
                ->whereIn('noteID', $notes->pluck('noteID')->all() ?: [0])
                ->where('is_viewed', false)
                ->update(['is_viewed' => true]);
        }

        $classSubject = ClassSubject::with('subject')->where('class_subjectID', (int) $classSubjectID)->first();

        return response()->json([
            'success' => true,
            'subject_name' => $classSubject->subject->subject_name ?? 'Somo',
            'notes' => $notes,
        ]);
    }

    private function notePayload(SubjectNote $n, bool $teacher): array
    {
        $n->loadMissing(['subject', 'teacher']);

        return [
            'noteID' => $n->noteID,
            'title' => $n->title,
            'original_filename' => $n->original_filename,
            'mime_type' => $n->mime_type,
            'file_size' => $n->file_size,
            'subject_name' => optional($n->subject)->subject_name ?? 'Somo',
            'teacher_name' => $n->teacher
                ? trim(($n->teacher->first_name ?? '').' '.($n->teacher->last_name ?? ''))
                : '-',
            'created_at' => optional($n->created_at)->format('d/m/Y H:i'),
            'view_url' => route('subject_notes.view', $n->noteID),
            'download_url' => route('subject_notes.download', $n->noteID),
            'can_manage' => $teacher,
        ];
    }

    private function createNoteFromUpload(
        $file,
        string $title,
        ClassSubject $classSubject,
        int $schoolID,
        int $teacherID,
        int $yearId,
        string $uploadPath,
        int $index = 0
    ): SubjectNote {
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            $ext = 'pdf';
        }
        $safeOriginal = substr(preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: ('note.'.$ext), 0, 180);
        $fileName = time().'_'.$index.'_'.bin2hex(random_bytes(3)).'_'.$safeOriginal;
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0777, true);
        }
        $file->move($uploadPath, $fileName);

        $payload = [
            'schoolID' => $schoolID,
            'academic_yearID' => $yearId,
            'subjectID' => $classSubject->subjectID,
            'class_subjectID' => $classSubject->class_subjectID,
            'teacherID' => $teacherID,
            'title' => $title,
            'file_path' => 'teacher_notes/'.$fileName,
            'original_filename' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
        ];
        if (Schema::hasColumn('subject_notes', 'level')) {
            $payload['level'] = $this->levelFromClassSubject($classSubject, $schoolID);
        }

        $note = SubjectNote::create($payload);

        $this->notifyParents($note);

        return $note;
    }

    private function notifyParents(SubjectNote $note): void
    {
        if (!Schema::hasTable('parent_subject_note_notifications')) {
            return;
        }

        $note->loadMissing(['classSubject']);
        $cs = $note->classSubject;
        if (!$cs) {
            return;
        }

        $studentQuery = Student::where('schoolID', $note->schoolID)
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

        $students = $studentQuery->get(['studentID', 'parentID']);
        foreach ($students as $student) {
            if (!$student->parentID) {
                continue;
            }
            ParentSubjectNoteNotification::updateOrCreate(
                [
                    'parentID' => (int) $student->parentID,
                    'studentID' => (int) $student->studentID,
                    'noteID' => $note->noteID,
                ],
                [
                    'schoolID' => $note->schoolID,
                    'is_viewed' => false,
                ]
            );
        }
    }

    private function authorizeNoteAccess(int $noteID): ?SubjectNote
    {
        $schoolID = (int) Session::get('schoolID');
        $userType = strtolower((string) Session::get('user_type'));
        $teacherID = (int) Session::get('teacherID');
        $parentID = (int) Session::get('parentID');

        $note = SubjectNote::where('noteID', $noteID)->where('schoolID', $schoolID)->first();
        if (!$note) {
            return null;
        }

        if (in_array($userType, ['admin', 'staff'], true)) {
            return $note;
        }
        if ($userType === 'teacher') {
            return (int) $note->teacherID === $teacherID ? $note : null;
        }
        if ($userType === 'parent' && $parentID) {
            $ok = ParentSubjectNoteNotification::where('schoolID', $schoolID)
                ->where('parentID', $parentID)
                ->where('noteID', $note->noteID)
                ->exists();
            if ($ok) {
                return $note;
            }
            $studentIds = Student::where('parentID', $parentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->pluck('studentID');
            foreach (Student::whereIn('studentID', $studentIds)->get() as $student) {
                if (in_array((int) $note->class_subjectID, $this->classSubjectsForStudent($student), true)) {
                    return $note;
                }
            }
        }

        return null;
    }

    private function serveNoteFile(int $noteID, bool $inline)
    {
        try {
            $note = $this->authorizeNoteAccess($noteID);
            if (!$note || !$note->file_path) {
                abort(404, 'File not found');
            }
            $foundPath = $this->resolveExistingPath($note->file_path);
            if (!$foundPath) {
                abort(404, 'File not found');
            }
            $fileName = $note->original_filename ?: basename($note->file_path);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $foundPath) ?: ($note->mime_type ?: 'application/octet-stream');
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
            Log::error('Error serving subject note: '.$e->getMessage());
            abort(404, 'File not found');
        }
    }

    private function resolveUploadPath(): string
    {
        $folder = 'teacher_notes';
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
            if (!is_dir($candidate)) {
                @mkdir($candidate, 0777, true);
            }
            if (is_dir($candidate) && is_writable($candidate)) {
                return $candidate;
            }
        }

        $fallback = public_path($folder);
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0777, true);
        }

        return $fallback;
    }

    private function resolveExistingPath(string $relativePath): ?string
    {
        $scriptPath = isset($_SERVER['SCRIPT_FILENAME'])
            ? dirname($_SERVER['SCRIPT_FILENAME']).'/'.$relativePath
            : null;
        $parentDir = dirname(base_path());
        $paths = [
            $scriptPath,
            public_path($relativePath),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'].'/'.$relativePath : null,
            Storage::disk('public')->path($relativePath),
            $parentDir.'/public_html/'.$relativePath,
        ];
        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function deleteNoteFile(string $relativePath, string $uploadPath): void
    {
        $oldFilePath = $uploadPath.'/'.basename($relativePath);
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
        $resolved = $this->resolveExistingPath($relativePath);
        if ($resolved && file_exists($resolved)) {
            @unlink($resolved);
        }
    }
}
