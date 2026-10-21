<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\ParentSubjectNoteNotification;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\SubjectNote;
use App\Services\SelectedAcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SubjectNoteController extends Controller
{
    public const KIND_NOTE = 'note';
    public const KIND_PAST_PAPER = 'past_paper';

    private function ayService(): SelectedAcademicYearService
    {
        return app(SelectedAcademicYearService::class);
    }

    private function hasKindColumn(): bool
    {
        return Schema::hasTable('subject_notes') && Schema::hasColumn('subject_notes', 'kind');
    }

    private function currentKind(?Request $request = null): string
    {
        $request = $request ?: request();
        if ($request->routeIs('admin.past_papers*') || $request->routeIs('parentPastPapers*')) {
            return self::KIND_PAST_PAPER;
        }
        $raw = strtolower(trim((string) $request->input('kind', '')));

        return $raw === self::KIND_PAST_PAPER ? self::KIND_PAST_PAPER : self::KIND_NOTE;
    }

    private function applyKind($query, string $kind)
    {
        if ($this->hasKindColumn()) {
            $query->where('kind', $kind);
        } elseif ($kind !== self::KIND_NOTE) {
            $query->whereRaw('1=0');
        }

        return $query;
    }

    private function kindLabels(string $kind): array
    {
        if ($kind === self::KIND_PAST_PAPER) {
            return [
                'kind' => self::KIND_PAST_PAPER,
                'page' => 'PAST PAPERS',
                'hint' => 'Teacher past papers (PDF / DOC) for the selected academic year',
                'filter' => 'Filter past papers',
                'table' => 'Subjects',
                'count_col' => 'Past papers (PDF / DOC)',
                'empty' => 'Select a main class to see subjects.',
                'empty_filter' => 'No subjects found for this filter.',
                'loading' => 'Loading subjects...',
                'no_items' => 'No past papers uploaded for this subject.',
                'count_word' => 'past paper',
                'count_word_plural' => 'past papers',
                'icon' => 'fa-file-text',
            ];
        }

        return [
            'kind' => self::KIND_NOTE,
            'page' => 'NOTES',
            'hint' => 'Teacher notes (PDF / DOC) for the selected academic year',
            'filter' => 'Filter notes',
            'table' => 'Subjects',
            'count_col' => 'Notes (PDF / DOC)',
            'empty' => 'Select a main class to see subjects.',
            'empty_filter' => 'No subjects found for this filter.',
            'loading' => 'Loading subjects...',
            'no_items' => 'No notes uploaded for this subject.',
            'count_word' => 'note',
            'count_word_plural' => 'notes',
            'icon' => 'fa-sticky-note',
        ];
    }

    private function constrainNotificationKind($query, string $kind)
    {
        if (!Schema::hasTable('subject_notes')) {
            $query->whereRaw('1=0');
            return $query;
        }
        if (!$this->hasKindColumn()) {
            if ($kind !== self::KIND_NOTE) {
                $query->whereRaw('1=0');
            }
            return $query;
        }

        $query->whereHas('note', function ($q) use ($kind) {
            $q->where('kind', $kind);
        });

        return $query;
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
        $kind = $this->currentKind($request);
        $streams = $this->teacherSubjectStreams($classSubject, $teacherID);
        $canApplyAll = (int) ($classSubject->subclassID ?? 0) > 0 && $streams->count() > 1;
        $streamPayload = [
            'streams' => $canApplyAll
                ? $streams->map(fn (ClassSubject $cs) => $this->streamInfo($cs, (int) $classSubject->class_subjectID))->values()
                : [],
            'streams_label' => $canApplyAll ? $this->formatStreamsLabel($streams) : '',
            'apply_to_all_available' => $canApplyAll,
        ];
        if (!Schema::hasTable('subject_notes')) {
            return response()->json(array_merge([
                'success' => true,
                'notes' => [],
                'subject_name' => $classSubject->subject->subject_name ?? 'Somo',
            ], $streamPayload));
        }
        $query = SubjectNote::with(['subject', 'teacher'])
            ->where('schoolID', $schoolID)
            ->where('teacherID', $teacherID)
            ->where('class_subjectID', $classSubject->class_subjectID);
        $this->applyKind($query, $kind);
        if ($yearId) {
            $query->where('academic_yearID', $yearId);
        }

        $notes = $query->orderByDesc('created_at')->get()->map(function (SubjectNote $n) {
            return $this->notePayload($n, true);
        });

        return response()->json(array_merge([
            'success' => true,
            'notes' => $notes,
            'subject_name' => $classSubject->subject->subject_name ?? 'Somo',
        ], $streamPayload));
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
            $targets = collect([$classSubject]);
            if ($request->boolean('apply_to_all') && (int) ($classSubject->subclassID ?? 0) > 0) {
                $siblings = $this->teacherSubjectStreams($classSubject, $teacherID);
                if ($siblings->count() > 1) {
                    $targets = $siblings;
                }
            }

            $saved = [];
            $uploadPath = $this->resolveUploadPath();
            foreach ($items as $index => $item) {
                $title = $item['title'];
                if ($title === '') {
                    return response()->json(['error' => 'Each note needs a title / topic range'], 422);
                }
                $stored = $this->storeUploadedFile($item['file'], $uploadPath, $index);
                foreach ($targets->values() as $tIndex => $targetCs) {
                    $fileForNote = $tIndex === 0
                        ? $stored
                        : $this->duplicateStoredFile($stored, $uploadPath, ($index * 100) + $tIndex);
                    $saved[] = $this->createNoteFromStored(
                        $fileForNote,
                        $title,
                        $targetCs,
                        $schoolID,
                        $teacherID,
                        $yearId,
                        $this->currentKind($request),
                        $tIndex === 0
                    );
                }
            }

            $noteCount = count($items);
            $streamsLabel = $this->formatStreamsLabel($targets);
            $isPaper = $this->currentKind($request) === self::KIND_PAST_PAPER;
            $itemWordPlural = $isPaper ? 'past papers' : 'notes';
            if ($targets->count() > 1) {
                $message = $noteCount === 1
                    ? 'Saved '.$streamsLabel
                    : $noteCount.' '.$itemWordPlural.' saved '.$streamsLabel;
            } else {
                $message = $noteCount === 1
                    ? ($isPaper ? 'Past paper saved successfully' : 'Note saved successfully')
                    : $noteCount.' '.$itemWordPlural.' saved successfully';
            }

            $currentSaved = array_values(array_filter($saved, function (SubjectNote $n) use ($classSubject) {
                return (int) $n->class_subjectID === (int) $classSubject->class_subjectID;
            }));

            return response()->json([
                'success' => true,
                'message' => $message,
                'streams_label' => $streamsLabel,
                'notes' => array_map(fn (SubjectNote $n) => $this->notePayload($n, true), $currentSaved ?: $saved),
                'note' => $this->notePayload(($currentSaved[0] ?? $saved[0]), true),
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

        $deletedKind = $this->hasKindColumn() ? (string) ($note->kind ?? self::KIND_NOTE) : self::KIND_NOTE;

        return response()->json([
            'success' => true,
            'message' => $deletedKind === self::KIND_PAST_PAPER
                ? 'Past paper deleted successfully'
                : 'Note deleted successfully',
        ]);
    }

    public function viewFile($noteID)
    {
        return $this->serveNoteFile((int) $noteID, true);
    }

    public function downloadFile($noteID)
    {
        return $this->serveNoteFile((int) $noteID, false);
    }

    public function parentIndex(Request $request)
    {
        $userType = strtolower((string) Session::get('user_type'));
        $schoolID = (int) Session::get('schoolID');
        $parentID = (int) Session::get('parentID');
        if ($userType !== 'parent' || !$schoolID || !$parentID) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $locale = Session::get('locale', 'sw');
        app()->setLocale($locale);
        $kind = $this->currentKind($request);

        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $notesReady = Schema::hasTable('subject_notes') && Schema::hasTable('parent_subject_note_notifications');
        $students = Student::where('parentID', $parentID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['subclass.class'])
            ->orderBy('first_name')
            ->get();

        $children = $students->map(function (Student $student) use ($schoolID, $parentID, $yearIds, $notesReady, $kind) {
            $csIds = $this->classSubjectsForStudent($student);
            $noteCount = 0;
            $unread = 0;
            if ($notesReady) {
                $notesQuery = SubjectNote::where('schoolID', $schoolID);
                $this->applyKind($notesQuery, $kind);
                if (!empty($csIds)) {
                    $notesQuery->whereIn('class_subjectID', $csIds);
                } else {
                    $notesQuery->whereRaw('1=0');
                }
                if (!empty($yearIds)) {
                    $notesQuery->whereIn('academic_yearID', $yearIds);
                }
                $noteCount = $notesQuery->count();
                $unreadQ = ParentSubjectNoteNotification::where('schoolID', $schoolID)
                    ->where('parentID', $parentID)
                    ->where('studentID', $student->studentID)
                    ->where('is_viewed', false);
                $this->constrainNotificationKind($unreadQ, $kind);
                $unread = $unreadQ->count();
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

        return view('Parents.subject_notes', compact('children', 'locale', 'kind'));
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

        $kind = $this->currentKind($request);
        $csIds = $classSubjects->pluck('class_subjectID')->map(fn ($id) => (int) $id)->all();
        $noteCounts = [];
        $unreadBySubject = [];
        if (!empty($csIds) && Schema::hasTable('subject_notes')) {
            $q = SubjectNote::where('schoolID', $schoolID)->whereIn('class_subjectID', $csIds);
            $this->applyKind($q, $kind);
            if (!empty($yearIds)) {
                $q->whereIn('academic_yearID', $yearIds);
            }
            $noteCounts = $q->selectRaw('class_subjectID, COUNT(*) as total')
                ->groupBy('class_subjectID')
                ->pluck('total', 'class_subjectID')
                ->all();
        }

        if (!empty($csIds) && Schema::hasTable('parent_subject_note_notifications') && Schema::hasTable('subject_notes')) {
            $unreadQ = ParentSubjectNoteNotification::query()
                ->join('subject_notes', 'subject_notes.noteID', '=', 'parent_subject_note_notifications.noteID')
                ->where('parent_subject_note_notifications.schoolID', $schoolID)
                ->where('parent_subject_note_notifications.parentID', $parentID)
                ->where('parent_subject_note_notifications.studentID', $student->studentID)
                ->where('parent_subject_note_notifications.is_viewed', false)
                ->whereIn('subject_notes.class_subjectID', $csIds);
            if ($this->hasKindColumn()) {
                $unreadQ->where('subject_notes.kind', $kind);
            } elseif ($kind !== self::KIND_NOTE) {
                $unreadQ->whereRaw('1=0');
            }
            $unreadBySubject = $unreadQ
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

        $kind = $this->currentKind($request);
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
        $this->applyKind($query, $kind);
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

    public function adminIndex(Request $request)
    {
        $schoolID = $this->adminSchoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $kind = $this->currentKind($request);
        $labels = $this->kindLabels($kind);
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get(['classID', 'class_name']);

        $yearLabel = $this->adminYearLabel($schoolID);

        return view('Admin.subject_notes', compact('classes', 'yearLabel', 'kind', 'labels'));
    }

    public function adminSubjects(Request $request)
    {
        $schoolID = $this->adminSchoolId();
        if (!$schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $classID = (int) $request->input('classID', 0);
        $subclassID = (int) $request->input('subclassID', 0);
        $status = trim((string) $request->input('student_status', ''));
        if (!in_array($status, ['Required', 'Optional'], true)) {
            $status = '';
        }

        if ($classID < 1) {
            return response()->json(['error' => 'Please select a class'], 422);
        }

        $class = ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->first();
        if (!$class) {
            return response()->json(['error' => 'Class not found'], 404);
        }

        $subclassIds = Subclass::where('classID', $classID)->pluck('subclassID')->map(fn ($id) => (int) $id)->all();
        $query = ClassSubject::with(['subject', 'teacher', 'subclass.class'])
            ->where('status', 'Active')
            ->where(function ($q) use ($classID, $subclassID, $subclassIds) {
                if ($subclassID > 0) {
                    $q->where('subclassID', $subclassID)
                        ->orWhere(function ($q2) use ($classID) {
                            $q2->whereNull('subclassID')->where('classID', $classID);
                        });
                    return;
                }
                $q->where('classID', $classID);
                if (!empty($subclassIds)) {
                    $q->orWhereIn('subclassID', $subclassIds);
                }
            });

        if ($status === 'Optional') {
            $query->where('student_status', 'Optional');
        } elseif ($status === 'Required') {
            $query->where(function ($q) {
                $q->where('student_status', 'Required')->orWhereNull('student_status');
            });
        }

        $kind = $this->currentKind($request);
        $classSubjects = $query->get();
        $csIds = $classSubjects->pluck('class_subjectID')->map(fn ($id) => (int) $id)->all();
        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        $noteRows = collect();
        $noteCounts = [];
        if (!empty($csIds) && Schema::hasTable('subject_notes')) {
            $nq = SubjectNote::where('schoolID', $schoolID)->whereIn('class_subjectID', $csIds);
            $this->applyKind($nq, $kind);
            if (!empty($yearIds)) {
                $nq->whereIn('academic_yearID', $yearIds);
            }
            $noteRows = $nq->get(['class_subjectID', 'title', 'original_filename']);
            $noteCounts = $noteRows->groupBy('class_subjectID')->map->count()->all();
        }

        $mapped = $classSubjects->map(function (ClassSubject $cs) use ($noteCounts, $class) {
            $teacher = $cs->teacher;
            $gender = strtolower((string) ($teacher->gender ?? 'male'));
            $photo = $teacher && $teacher->image
                ? asset('userImages/'.$teacher->image)
                : asset($gender === 'female' ? 'images/female.png' : 'images/male.png');
            $statusLabel = strtolower((string) ($cs->student_status ?? 'Required')) === 'optional'
                ? 'Optional'
                : 'Required';
            $subclassName = $cs->subclass
                ? trim((string) ($cs->subclass->subclass_name ?? ''))
                : '';

            return [
                'class_subjectID' => (int) $cs->class_subjectID,
                'class_subjectIDs' => [(int) $cs->class_subjectID],
                'subjectID' => (int) ($cs->subjectID ?? 0),
                'teacherID' => (int) ($cs->teacherID ?? 0),
                'subclassID' => (int) ($cs->subclassID ?? 0),
                'subject_name' => $cs->subject->subject_name ?? 'Subject',
                'subject_code' => $cs->subject->subject_code ?? null,
                'subclass_name' => $subclassName !== '' ? $subclassName : 'All',
                'class_name' => $cs->subclass && $cs->subclass->class
                    ? $cs->subclass->class->class_name
                    : ($class->class_name ?? ''),
                'student_status' => $statusLabel,
                'teacher_name' => $teacher
                    ? trim(($teacher->first_name ?? '').' '.($teacher->middle_name ? $teacher->middle_name.' ' : '').($teacher->last_name ?? ''))
                    : 'Not assigned',
                'teacher_photo' => $photo,
                'notes_count' => (int) ($noteCounts[(int) $cs->class_subjectID] ?? 0),
            ];
        });

        $subjects = ($subclassID > 0
            ? $mapped
            : $this->groupAdminSubjectsByTeacherStreams($mapped, $noteRows)
        )->sortBy(function ($row) {
            return mb_strtolower(($row['subject_name'] ?? '').' '.($row['subclass_name'] ?? ''));
        }, SORT_NATURAL)->values();

        $subclasses = Subclass::where('classID', $classID)
            ->orderBy('subclass_name')
            ->get()
            ->filter(function ($sub) use ($subclassIds) {
                if (count($subclassIds) === 1 && trim((string) $sub->subclass_name) === '') {
                    return false;
                }
                return true;
            })
            ->map(function ($sub) {
                return [
                    'subclassID' => (int) $sub->subclassID,
                    'subclass_name' => trim((string) $sub->subclass_name) !== ''
                        ? $sub->subclass_name
                        : 'Stream',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'class_name' => $class->class_name,
            'subclasses' => $subclasses,
            'subjects' => $subjects,
        ]);
    }

    public function adminSubjectNotes(Request $request, $classSubjectID)
    {
        $schoolID = $this->adminSchoolId();
        if (!$schoolID) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $idList = array_values(array_unique(array_filter(array_map(
            'intval',
            explode(',', (string) ($request->input('ids') ?: $classSubjectID))
        ), fn ($id) => $id > 0)));
        if (empty($idList)) {
            $idList = [(int) $classSubjectID];
        }

        $classSubjects = ClassSubject::with(['subject', 'class', 'subclass.class'])
            ->whereIn('class_subjectID', $idList)
            ->where('status', 'Active')
            ->where(function ($q) use ($schoolID) {
                $q->whereHas('class', function ($cq) use ($schoolID) {
                    $cq->where('schoolID', $schoolID);
                })->orWhereHas('subclass.class', function ($cq) use ($schoolID) {
                    $cq->where('schoolID', $schoolID);
                });
            })
            ->get();
        if ($classSubjects->isEmpty()) {
            return response()->json(['error' => 'Subject not found'], 404);
        }

        $allowedIds = $classSubjects->pluck('class_subjectID')->map(fn ($id) => (int) $id)->all();
        $first = $classSubjects->first();
        $yearIds = $this->ayService()->selectedYearIds($schoolID);
        if (!Schema::hasTable('subject_notes')) {
            return response()->json([
                'success' => true,
                'subject_name' => $first->subject->subject_name ?? 'Subject',
                'streams_label' => $this->formatStreamsLabel($classSubjects),
                'notes' => [],
            ]);
        }

        $kind = $this->currentKind($request);
        $query = SubjectNote::with(['subject', 'teacher', 'classSubject.subclass', 'classSubject.class'])
            ->where('schoolID', $schoolID)
            ->whereIn('class_subjectID', $allowedIds);
        $this->applyKind($query, $kind);
        if (!empty($yearIds)) {
            $query->whereIn('academic_yearID', $yearIds);
        }

        $grouped = [];
        foreach ($query->orderByDesc('created_at')->get() as $n) {
            $key = mb_strtolower(trim((string) $n->title).'|'.trim((string) ($n->original_filename ?? '')));
            $stream = trim((string) (optional($n->classSubject->subclass)->subclass_name ?? ''));
            if (!isset($grouped[$key])) {
                $payload = $this->notePayload($n, false);
                $payload['streams'] = [];
                $grouped[$key] = $payload;
            }
            if ($stream !== '' && !in_array($stream, $grouped[$key]['streams'], true)) {
                $grouped[$key]['streams'][] = $stream;
            }
        }

        return response()->json([
            'success' => true,
            'subject_name' => $first->subject->subject_name ?? 'Subject',
            'streams_label' => $this->formatStreamsLabel($classSubjects),
            'notes' => array_values($grouped),
        ]);
    }

    private function adminSchoolId(): ?int
    {
        $userType = strtolower((string) Session::get('user_type'));
        $schoolID = (int) Session::get('schoolID');
        if (!in_array($userType, ['admin', 'staff'], true) || $schoolID < 1) {
            return null;
        }

        return $schoolID;
    }

    private function adminYearLabel(int $schoolID): string
    {
        $ctx = $this->ayService()->headerContext($schoolID);
        $years = $ctx['years'] ?? collect();
        $selected = $ctx['selected_year_ids'] ?? [];
        if ($years instanceof \Illuminate\Support\Collection && !empty($selected)) {
            $labels = $years->whereIn('academic_yearID', $selected)
                ->map(fn ($y) => $y->year_name ?: $y->year)
                ->filter()
                ->all();
            if (!empty($labels)) {
                return implode(', ', $labels);
            }
        }

        return 'Selected academic year';
    }

    private function notePayload(SubjectNote $n, bool $teacher): array
    {
        $n->loadMissing(['subject', 'teacher']);

        $mime = strtolower((string) ($n->mime_type ?? ''));
        $filename = strtolower((string) ($n->original_filename ?? ''));
        $isPdf = str_contains($mime, 'pdf') || str_ends_with($filename, '.pdf');
        $isDoc = str_contains($mime, 'word')
            || str_contains($mime, 'msword')
            || str_contains($mime, 'officedocument')
            || str_ends_with($filename, '.doc')
            || str_ends_with($filename, '.docx');

        return [
            'noteID' => $n->noteID,
            'title' => $n->title,
            'original_filename' => $n->original_filename,
            'mime_type' => $n->mime_type,
            'file_kind' => $isPdf ? 'PDF' : ($isDoc ? 'DOC' : 'FILE'),
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

    private function teacherSubjectStreams(ClassSubject $classSubject, int $teacherID)
    {
        $classID = (int) ($classSubject->classID ?: optional($classSubject->subclass)->classID);
        $subjectID = (int) $classSubject->subjectID;
        if ($classID < 1 || $subjectID < 1) {
            return collect([$classSubject]);
        }

        return ClassSubject::with(['subject', 'class', 'subclass.class'])
            ->where('status', 'Active')
            ->where('subjectID', $subjectID)
            ->whereNotNull('subclassID')
            ->where(function ($q) use ($teacherID) {
                $q->where('teacherID', $teacherID)
                    ->orWhereHas('componentTeachers', function ($cq) use ($teacherID) {
                        $cq->where('teacherID', $teacherID);
                    });
            })
            ->where(function ($q) use ($classID) {
                $q->where('classID', $classID)
                    ->orWhereHas('subclass', function ($sq) use ($classID) {
                        $sq->where('classID', $classID);
                    });
            })
            ->get();
    }

    private function streamInfo(ClassSubject $cs, int $currentId): array
    {
        $className = optional($cs->class)->class_name
            ?? optional(optional($cs->subclass)->class)->class_name
            ?? '';
        $stream = trim((string) optional($cs->subclass)->subclass_name);

        return [
            'class_subjectID' => (int) $cs->class_subjectID,
            'class_name' => $className,
            'stream' => $stream !== '' ? $stream : 'All',
            'label' => trim($className.' '.($stream !== '' ? $stream : '')),
            'is_current' => (int) $cs->class_subjectID === $currentId,
        ];
    }

    private function formatStreamsLabel($subjects): string
    {
        $className = '';
        $streams = [];
        foreach ($subjects as $cs) {
            if ($className === '') {
                $className = optional($cs->class)->class_name
                    ?? optional(optional($cs->subclass)->class)->class_name
                    ?? '';
            }
            $stream = trim((string) optional($cs->subclass)->subclass_name);
            if ($stream !== '' && !in_array($stream, $streams, true)) {
                $streams[] = $stream;
            }
        }
        natsort($streams);
        $streams = array_values($streams);
        if (empty($streams)) {
            return $className;
        }

        return trim($className.' '.implode(', ', $streams));
    }

    private function groupAdminSubjectsByTeacherStreams($mapped, $noteRows)
    {
        $grouped = collect();
        $mapped->groupBy(function ($row) {
            $sid = (int) ($row['subclassID'] ?? 0);
            if ($sid < 1) {
                return 'solo-'.((int) ($row['class_subjectID'] ?? 0));
            }

            return ((int) ($row['subjectID'] ?? 0)).'|'.((int) ($row['teacherID'] ?? 0)).'|'.($row['student_status'] ?? 'Required');
        })->each(function ($rows) use ($grouped, $noteRows) {
            $streamRows = $rows->filter(fn ($row) => (int) ($row['subclassID'] ?? 0) > 0)->values();
            if ($streamRows->count() < 2) {
                foreach ($rows as $row) {
                    $grouped->push($row);
                }
                return;
            }

            $first = $streamRows->first();
            $ids = $streamRows->pluck('class_subjectID')->map(fn ($id) => (int) $id)->all();
            $streamNames = $streamRows->pluck('subclass_name')
                ->filter(fn ($name) => $name !== '' && $name !== 'All')
                ->unique()
                ->sortBy(fn ($name) => $name, SORT_NATURAL)
                ->values();
            $uniqueNotes = $noteRows
                ->whereIn('class_subjectID', $ids)
                ->map(fn ($n) => mb_strtolower(trim((string) $n->title).'|'.trim((string) ($n->original_filename ?? ''))))
                ->unique()
                ->count();
            $first['class_subjectIDs'] = $ids;
            $first['subclass_name'] = $streamNames->implode(', ');
            $first['notes_count'] = $uniqueNotes;
            $grouped->push($first);
        });

        return $grouped;
    }

    private function storeUploadedFile($file, string $uploadPath, int $index): array
    {
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

        return [
            'relative' => 'teacher_notes/'.$fileName,
            'absolute' => $uploadPath.DIRECTORY_SEPARATOR.$fileName,
            'original' => $originalName,
            'mime' => $mimeType,
            'size' => $fileSize,
        ];
    }

    private function duplicateStoredFile(array $stored, string $uploadPath, int $index): array
    {
        $ext = strtolower((string) pathinfo($stored['absolute'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            $ext = 'pdf';
        }
        $safeOriginal = substr(preg_replace('/[^A-Za-z0-9._-]/', '_', $stored['original'] ?? ('note.'.$ext)) ?: ('note.'.$ext), 0, 180);
        $fileName = time().'_'.$index.'_'.bin2hex(random_bytes(3)).'_'.$safeOriginal;
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0777, true);
        }
        $copied = @copy($stored['absolute'], $uploadPath.DIRECTORY_SEPARATOR.$fileName);
        if (!$copied || !is_file($uploadPath.DIRECTORY_SEPARATOR.$fileName)) {
            throw new \RuntimeException('Failed to copy the note file to other streams');
        }

        $copy = $stored;
        $copy['relative'] = 'teacher_notes/'.$fileName;
        $copy['absolute'] = $uploadPath.DIRECTORY_SEPARATOR.$fileName;

        return $copy;
    }

    private function createNoteFromStored(
        array $stored,
        string $title,
        ClassSubject $classSubject,
        int $schoolID,
        int $teacherID,
        int $yearId,
        string $kind = self::KIND_NOTE,
        bool $notifyAdmin = true
    ): SubjectNote {
        $payload = [
            'schoolID' => $schoolID,
            'academic_yearID' => $yearId,
            'subjectID' => $classSubject->subjectID,
            'class_subjectID' => $classSubject->class_subjectID,
            'teacherID' => $teacherID,
            'title' => $title,
            'file_path' => $stored['relative'],
            'original_filename' => $stored['original'],
            'mime_type' => $stored['mime'],
            'file_size' => $stored['size'],
        ];
        if (Schema::hasColumn('subject_notes', 'level')) {
            $payload['level'] = $this->levelFromClassSubject($classSubject, $schoolID);
        }
        if ($this->hasKindColumn()) {
            $payload['kind'] = $kind === self::KIND_PAST_PAPER ? self::KIND_PAST_PAPER : self::KIND_NOTE;
        }
        if (Schema::hasColumn('subject_notes', 'is_read_by_admin')) {
            $payload['is_read_by_admin'] = !$notifyAdmin;
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
