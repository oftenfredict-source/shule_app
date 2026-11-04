<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\School;
use App\Models\SchoolSubject;
use App\Models\Student;
use App\Models\StudentPortalBook;
use App\Models\StudentPortalNote;
use App\Models\StudentPortalQuiz;
use App\Models\StudentPortalQuizAttempt;
use App\Models\StudentPortalQuizQuestion;
use App\Models\Subclass;
use App\Support\StudentPortalQuizScoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class StudentPortalMaterialController extends Controller
{
    public function index()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!in_array($userType, ['Admin', 'Teacher'], true) || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $school = School::find($schoolID);
        $enabled = $school && $school->isStudentPortalEnabled();
        $placement = $enabled
            ? $this->placementOptionsForUser($userType, (int) $schoolID)
            : [
                'classes' => collect(),
                'subclasses' => collect(),
                'subjects' => collect(),
                'subjectClassMap' => [],
            ];
        $classes = $placement['classes'];
        $subclasses = $placement['subclasses'];
        $subjects = $placement['subjects'];
        $subjectClassMap = $placement['subjectClassMap'];

        $notes = $enabled ? StudentPortalNote::where('schoolID', $schoolID)->latest()->get() : collect();
        $books = $enabled ? StudentPortalBook::where('schoolID', $schoolID)->latest()->get() : collect();
        $quizzes = $enabled
            ? StudentPortalQuiz::with(['questions', 'attempts'])->where('schoolID', $schoolID)->latest()->get()
            : collect();

        $attemptStudentIds = $quizzes->flatMap(fn ($q) => $q->attempts->pluck('studentID'))->unique()->filter();
        $studentsById = $attemptStudentIds->isNotEmpty()
            ? Student::where('schoolID', $schoolID)->whereIn('studentID', $attemptStudentIds)->get()->keyBy('studentID')
            : collect();

        $editingQuiz = null;
        $editId = (int) request()->query('edit', 0);
        if ($enabled && $editId > 0) {
            $found = $quizzes->firstWhere('id', $editId);
            if ($found) {
                $editingQuiz = $this->quizEditorPayload($found);
            }
        }

        return view('Teacher.student_portal_materials', [
            'school' => $school,
            'enabled' => $enabled,
            'classes' => $classes,
            'subclasses' => $subclasses,
            'subjects' => $subjects,
            'subjectClassMap' => $subjectClassMap,
            'notes' => $notes,
            'books' => $books,
            'quizzes' => $quizzes,
            'editingQuiz' => $editingQuiz,
            'studentsById' => $studentsById,
            'user_type' => $userType,
            'uploadLimit' => ini_get('upload_max_filesize') ?: '2M',
            'postLimit' => ini_get('post_max_size') ?: '8M',
            'stats' => [
                'notes' => $notes->count(),
                'books' => $books->count(),
                'quizzes' => $quizzes->count(),
                'attempts' => $quizzes->sum(fn ($q) => $q->attempts->count()),
            ],
        ]);
    }

    public function storeNote(Request $request)
    {
        [$school] = $this->guard();
        if ($redirect = $this->rejectBadUpload($request, 'notes')) {
            return $redirect;
        }
        $data = $this->validatePlacement($request);
        $request->validate([
            'title' => 'required|string|max:180',
            'body' => 'nullable|string|max:20000',
            'external_url' => 'nullable|string|max:500',
            'file' => 'nullable|file|extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,txt,mp4,mp3|max:262144',
        ]);
        $externalUrl = $this->normalizeExternalUrl($request->input('external_url'));
        if ($externalUrl === false) {
            return back()->with('error', 'Link si sahihi. Tumia URL kamili kama https://youtube.com/...')->withInput()->with('portal_tab', 'notes');
        }
        if (!trim((string) $request->input('body')) && !$request->hasFile('file') && !$externalUrl) {
            return back()->with('error', 'Andika maelezo, weka faili, au ongeza link (mfano YouTube).')->withInput()->with('portal_tab', 'notes');
        }

        $file = $this->storeFile($request, $school->schoolID);
        StudentPortalNote::create(array_merge($data, [
            'schoolID' => $school->schoolID,
            'teacherID' => Session::get('teacherID'),
            'title' => $request->title,
            'body' => $request->body,
            'file_path' => $file['path'] ?? null,
            'original_name' => $file['name'] ?? null,
            'external_url' => $externalUrl,
            'is_published' => true,
        ]));

        return back()->with('success', 'Note imehifadhiwa. Wanafunzi wa darasa hilo wataiweza kusoma.')->with('portal_tab', 'notes');
    }

    public function deleteNote($id)
    {
        [$school] = $this->guard();
        $note = StudentPortalNote::where('schoolID', $school->schoolID)->where('id', $id)->first();
        if ($note) {
            $this->deleteStoredFile($note->file_path);
            $note->delete();
        }

        return back()->with('success', 'Note imefutwa.')->with('portal_tab', 'notes');
    }

    public function toggleNote($id)
    {
        [$school] = $this->guard();
        $note = StudentPortalNote::where('schoolID', $school->schoolID)->where('id', $id)->firstOrFail();
        $note->is_published = !$note->is_published;
        $note->save();

        return back()->with('success', $note->is_published ? 'Note imechapishwa.' : 'Note imefichwa.')
            ->with('portal_tab', 'notes');
    }

    public function storeBook(Request $request)
    {
        [$school] = $this->guard();
        if ($redirect = $this->rejectBadUpload($request, 'files')) {
            return $redirect;
        }
        $data = $this->validatePlacement($request);
        $request->validate([
            'title' => 'required|string|max:180',
            'author' => 'nullable|string|max:160',
            'description' => 'nullable|string|max:20000',
            'external_url' => 'nullable|string|max:500',
            'file' => 'nullable|file|extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,txt,mp4,mp3,epub|max:262144',
        ]);
        $externalUrl = $this->normalizeExternalUrl($request->input('external_url'));
        if ($externalUrl === false) {
            return back()->with('error', 'Link si sahihi. Tumia URL kamili kama https://youtube.com/...')->withInput()->with('portal_tab', 'files');
        }
        if (!trim((string) $request->input('description')) && !$request->hasFile('file') && !$externalUrl) {
            return back()->with('error', 'Andika maelezo, weka faili, au ongeza link (mfano YouTube).')->withInput()->with('portal_tab', 'files');
        }

        $file = $this->storeFile($request, $school->schoolID);
        StudentPortalBook::create(array_merge($data, [
            'schoolID' => $school->schoolID,
            'teacherID' => Session::get('teacherID'),
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'file_path' => $file['path'] ?? null,
            'original_name' => $file['name'] ?? null,
            'external_url' => $externalUrl,
            'is_published' => true,
        ]));

        return back()->with('success', 'Nyenzo imehifadhiwa kwa student portal.')->with('portal_tab', 'files');
    }

    public function deleteBook($id)
    {
        [$school] = $this->guard();
        $book = StudentPortalBook::where('schoolID', $school->schoolID)->where('id', $id)->first();
        if ($book) {
            $this->deleteStoredFile($book->file_path);
            $book->delete();
        }

        return back()->with('success', 'Nyenzo imefutwa.')->with('portal_tab', 'files');
    }

    public function toggleBook($id)
    {
        [$school] = $this->guard();
        $book = StudentPortalBook::where('schoolID', $school->schoolID)->where('id', $id)->firstOrFail();
        $book->is_published = !$book->is_published;
        $book->save();

        return back()->with('success', $book->is_published ? 'Nyenzo imechapishwa.' : 'Nyenzo imefichwa.')
            ->with('portal_tab', 'files');
    }

    public function storeQuiz(Request $request)
    {
        [$school] = $this->guard();
        $data = $this->validatePlacement($request, Session::get('user_type') === 'Teacher');
        $request->validate([
            'title' => 'required|string|max:180',
            'instructions' => 'nullable|string|max:5000',
            'opens_at' => 'nullable|date|required_with:closes_at',
            'closes_at' => 'nullable|date|required_with:opens_at|after:opens_at',
            'sections' => 'required|array|min:1|max:20',
            'sections.*.question_type' => 'required|in:multiple_choice,true_false,matching,fill_blank,short_answer,essay',
            'sections.*.section_title' => 'nullable|string|max:200',
            'sections.*.instructions' => 'nullable|string|max:2000',
        ]);

        $rows = $this->buildQuizQuestionRows($request->input('sections', []));
        if ($rows === []) {
            throw ValidationException::withMessages([
                'sections' => 'Add at least one question item in your sections.',
            ]);
        }
        if (count($rows) > 80) {
            throw ValidationException::withMessages([
                'sections' => 'Too many items (max 80 across all sections).',
            ]);
        }

        DB::transaction(function () use ($request, $school, $data, $rows) {
            $quiz = StudentPortalQuiz::create(array_merge($data, [
                'schoolID' => $school->schoolID,
                'teacherID' => Session::get('teacherID'),
                'title' => $request->title,
                'instructions' => $request->instructions,
                'is_published' => true,
                'opens_at' => $request->filled('opens_at') ? $request->opens_at : null,
                'closes_at' => $request->filled('closes_at') ? $request->closes_at : null,
            ]));

            foreach ($rows as $index => $row) {
                $row['quiz_id'] = $quiz->id;
                $row['sort_order'] = $index + 1;
                StudentPortalQuizQuestion::create($row);
            }
        });

        $message = $request->filled('opens_at')
            ? 'Quiz imehifadhiwa. Itafunguka kwa mwanafunzi muda ulioweka, kisha ijifunge yenyewe.'
            : 'Quiz / mtihani umehifadhiwa (aina mbalimbali za maswali). Wanafunzi wanaweza kuufanya kwenye portal.';

        return back()->with('success', $message)
            ->with('portal_tab', 'quizzes');
    }

    public function updateQuiz(Request $request, $id)
    {
        [$school] = $this->guard();
        $quiz = StudentPortalQuiz::where('schoolID', $school->schoolID)->where('id', $id)->firstOrFail();
        $data = $this->validatePlacement($request, Session::get('user_type') === 'Teacher');
        $request->validate([
            'title' => 'required|string|max:180',
            'instructions' => 'nullable|string|max:5000',
            'opens_at' => 'nullable|date|required_with:closes_at',
            'closes_at' => 'nullable|date|required_with:opens_at|after:opens_at',
            'sections' => 'required|array|min:1|max:20',
            'sections.*.question_type' => 'required|in:multiple_choice,true_false,matching,fill_blank,short_answer,essay',
            'sections.*.section_title' => 'nullable|string|max:200',
            'sections.*.instructions' => 'nullable|string|max:2000',
        ]);

        $rows = $this->buildQuizQuestionRows($request->input('sections', []));
        if ($rows === []) {
            throw ValidationException::withMessages([
                'sections' => 'Add at least one question item in your sections.',
            ]);
        }
        if (count($rows) > 80) {
            throw ValidationException::withMessages([
                'sections' => 'Too many items (max 80 across all sections).',
            ]);
        }

        DB::transaction(function () use ($request, $quiz, $data, $rows) {
            $quiz->update(array_merge($data, [
                'title' => $request->title,
                'instructions' => $request->instructions,
                'opens_at' => $request->filled('opens_at') ? $request->opens_at : null,
                'closes_at' => $request->filled('closes_at') ? $request->closes_at : null,
            ]));

            StudentPortalQuizQuestion::where('quiz_id', $quiz->id)->delete();
            foreach ($rows as $index => $row) {
                $row['quiz_id'] = $quiz->id;
                $row['sort_order'] = $index + 1;
                StudentPortalQuizQuestion::create($row);
            }
        });

        return redirect()
            ->route('student_portal.materials')
            ->with('success', 'Quiz updated. Schedule, marks, and questions were saved.')
            ->with('portal_tab', 'quizzes');
    }

    /**
     * Payload for the teacher quiz editor (create/edit form).
     *
     * @return array<string, mixed>
     */
    private function quizEditorPayload(StudentPortalQuiz $quiz): array
    {
        $sections = [];
        $grouped = $quiz->questions->groupBy('section_number');

        foreach ($grouped as $items) {
            $first = $items->first();
            $type = $first->question_type ?: 'multiple_choice';
            $config = is_array($first->config) ? $first->config : [];
            $section = [
                'question_type' => $type,
                'section_title' => $first->section_title,
                'instructions' => $config['section_instructions'] ?? null,
                'points' => (float) ($first->points ?: 1),
                'items' => [],
                'left' => [],
                'right' => [],
            ];

            if ($type === 'matching') {
                $left = $config['left'] ?? [];
                $right = $config['right'] ?? [];
                $answers = $config['answers'] ?? [];
                $rightByKey = [];
                foreach ((array) $right as $r) {
                    if (is_array($r) && isset($r['key'])) {
                        $rightByKey[(string) $r['key']] = (string) ($r['text'] ?? '');
                    }
                }
                foreach ((array) $left as $l) {
                    if (!is_array($l)) {
                        continue;
                    }
                    $leftKey = (string) ($l['key'] ?? '');
                    $rightKey = (string) ($answers[$leftKey] ?? '');
                    $section['left'][] = (string) ($l['text'] ?? '');
                    $section['right'][] = $rightByKey[$rightKey] ?? '';
                }
                if (($config['section_instructions'] ?? null) === null
                    && $first->question_text
                    && $first->question_text !== 'Match items in column A with column B.') {
                    $section['instructions'] = $first->question_text;
                }
            } else {
                foreach ($items as $q) {
                    $qConfig = is_array($q->config) ? $q->config : [];
                    if (empty($section['instructions']) && !empty($qConfig['section_instructions'])) {
                        $section['instructions'] = $qConfig['section_instructions'];
                    }
                    $item = [
                        'question_text' => $q->question_text,
                        'points' => (float) ($q->points ?: 1),
                        'option_a' => $q->option_a,
                        'option_b' => $q->option_b,
                        'option_c' => $q->option_c,
                        'option_d' => $q->option_d,
                        'correct_option' => $q->correct_option,
                        'correct_answer' => null,
                        'alt_answers' => null,
                    ];
                    if ($type === 'fill_blank') {
                        $item['correct_answer'] = $qConfig['answer'] ?? $q->correct_option;
                        $alts = (array) ($qConfig['answers'] ?? []);
                        $main = (string) ($item['correct_answer'] ?? '');
                        $alts = array_values(array_filter($alts, fn ($a) => (string) $a !== $main));
                        $item['alt_answers'] = $alts !== [] ? implode(' | ', $alts) : null;
                    }
                    $section['items'][] = $item;
                }
            }

            $sections[] = $section;
        }

        return [
            'id' => $quiz->id,
            'classID' => $quiz->classID,
            'subclassID' => $quiz->subclassID,
            'subjectID' => $quiz->subjectID,
            'title' => $quiz->title,
            'instructions' => $quiz->instructions,
            'opens_at' => $quiz->opens_at ? $quiz->opens_at->format('Y-m-d\TH:i') : '',
            'closes_at' => $quiz->closes_at ? $quiz->closes_at->format('Y-m-d\TH:i') : '',
            'attempts_count' => $quiz->attempts->count(),
            'sections' => $sections,
        ];
    }

    /**
     * Normalize teacher section builder payload into question rows.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return list<array<string, mixed>>
     */
    private function buildQuizQuestionRows(array $sections): array
    {
        $rows = [];
        $sectionNo = 0;

        foreach (array_values($sections) as $section) {
            $type = (string) ($section['question_type'] ?? '');
            if ($type === '') {
                continue;
            }
            $sectionNo++;
            $title = trim((string) ($section['section_title'] ?? '')) ?: null;
            $sectionInstructions = trim((string) ($section['instructions'] ?? '')) ?: null;

            if ($type === 'matching') {
                $rawLeft = (array) ($section['left'] ?? []);
                $rawRight = (array) ($section['right'] ?? []);
                $pairs = [];
                foreach ($rawLeft as $i => $leftText) {
                    $leftText = trim((string) $leftText);
                    $rightText = trim((string) ($rawRight[$i] ?? ''));
                    if ($leftText === '' || $rightText === '') {
                        continue;
                    }
                    $pairs[] = [$leftText, $rightText];
                }
                if (count($pairs) < 2) {
                    continue;
                }
                $pairs = array_slice($pairs, 0, 10);

                $leftItems = [];
                $rightItems = [];
                $answers = [];
                foreach ($pairs as $i => [$leftText, $rightText]) {
                    $leftKey = (string) ($i + 1);
                    $rightKey = chr(97 + $i);
                    $leftItems[] = ['key' => $leftKey, 'text' => $leftText];
                    $rightItems[] = ['key' => $rightKey, 'text' => $rightText];
                    $answers[$leftKey] = $rightKey;
                }

                // Shuffle display order of column B (answers stay keyed)
                $displayRight = $rightItems;
                shuffle($displayRight);

                $matchPoints = isset($section['points']) && is_numeric($section['points'])
                    ? max(0.5, (float) $section['points'])
                    : (float) count($leftItems);

                $rows[] = [
                    'question_type' => 'matching',
                    'section_number' => $sectionNo,
                    'section_title' => $title ?: 'Matching',
                    'item_label' => null,
                    'points' => $matchPoints,
                    'question_text' => $sectionInstructions ?: 'Match items in column A with column B.',
                    'option_a' => null,
                    'option_b' => null,
                    'option_c' => null,
                    'option_d' => null,
                    'correct_option' => null,
                    'config' => [
                        'left' => $leftItems,
                        'right' => $displayRight,
                        'answers' => $answers,
                    ],
                ];
                continue;
            }

            $items = array_values((array) ($section['items'] ?? []));
            $itemIndex = 0;
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $text = trim((string) ($item['question_text'] ?? $item['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $itemIndex++;
                $label = $type === 'true_false'
                    ? StudentPortalQuizScoring::roman($itemIndex)
                    : (string) $itemIndex;
                $points = isset($item['points']) && is_numeric($item['points'])
                    ? max(0.5, (float) $item['points'])
                    : 1.0;

                if ($type === 'multiple_choice') {
                    $a = trim((string) ($item['option_a'] ?? ''));
                    $b = trim((string) ($item['option_b'] ?? ''));
                    if ($a === '' || $b === '') {
                        continue;
                    }
                    $correct = strtoupper(trim((string) ($item['correct_option'] ?? 'A')));
                    if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) {
                        $correct = 'A';
                    }
                    $rows[] = [
                        'question_type' => 'multiple_choice',
                        'section_number' => $sectionNo,
                        'section_title' => $title ?: 'Multiple choice',
                        'item_label' => $label,
                        'points' => $points,
                        'question_text' => $text,
                        'option_a' => $a,
                        'option_b' => $b,
                        'option_c' => trim((string) ($item['option_c'] ?? '')) ?: null,
                        'option_d' => trim((string) ($item['option_d'] ?? '')) ?: null,
                        'correct_option' => $correct,
                        'config' => $sectionInstructions ? ['section_instructions' => $sectionInstructions] : null,
                    ];
                    continue;
                }

                if ($type === 'true_false') {
                    $correct = strtolower(trim((string) ($item['correct_option'] ?? $item['correct'] ?? 'true')));
                    $correct = in_array($correct, ['false', 'f', '0'], true) ? 'false' : 'true';
                    $rows[] = [
                        'question_type' => 'true_false',
                        'section_number' => $sectionNo,
                        'section_title' => $title ?: 'True / False',
                        'item_label' => $label,
                        'points' => $points,
                        'question_text' => $text,
                        'option_a' => 'True',
                        'option_b' => 'False',
                        'option_c' => null,
                        'option_d' => null,
                        'correct_option' => $correct,
                        'config' => $sectionInstructions ? ['section_instructions' => $sectionInstructions] : null,
                    ];
                    continue;
                }

                if ($type === 'fill_blank') {
                    $answer = trim((string) ($item['correct_answer'] ?? $item['answer'] ?? ''));
                    if ($answer === '') {
                        continue;
                    }
                    $alts = array_values(array_filter(array_map('trim', preg_split('/\s*\|\s*/', (string) ($item['alt_answers'] ?? '')) ?: [])));
                    $rows[] = [
                        'question_type' => 'fill_blank',
                        'section_number' => $sectionNo,
                        'section_title' => $title ?: 'Fill in the blanks',
                        'item_label' => $label,
                        'points' => $points,
                        'question_text' => $text,
                        'option_a' => null,
                        'option_b' => null,
                        'option_c' => null,
                        'option_d' => null,
                        'correct_option' => $answer,
                        'config' => [
                            'answer' => $answer,
                            'answers' => array_values(array_unique(array_merge([$answer], $alts))),
                            'section_instructions' => $sectionInstructions,
                        ],
                    ];
                    continue;
                }

                // short_answer / essay
                $rows[] = [
                    'question_type' => $type,
                    'section_number' => $sectionNo,
                    'section_title' => $title ?: ($type === 'essay' ? 'Essay' : 'Short answers'),
                    'item_label' => $label,
                    'points' => $points,
                    'question_text' => $text,
                    'option_a' => null,
                    'option_b' => null,
                    'option_c' => null,
                    'option_d' => null,
                    'correct_option' => null,
                    'config' => [
                        'manual' => true,
                        'section_instructions' => $sectionInstructions,
                    ],
                ];
            }
        }

        return $rows;
    }

    public function deleteQuiz($id)
    {
        [$school] = $this->guard();
        $quiz = StudentPortalQuiz::where('schoolID', $school->schoolID)->where('id', $id)->first();
        if ($quiz) {
            StudentPortalQuizAttempt::where('quiz_id', $quiz->id)->delete();
            StudentPortalQuizQuestion::where('quiz_id', $quiz->id)->delete();
            $quiz->delete();
        }

        return back()->with('success', 'Quiz imefutwa.')->with('portal_tab', 'quizzes');
    }

    public function toggleQuiz($id)
    {
        [$school] = $this->guard();
        $quiz = StudentPortalQuiz::where('schoolID', $school->schoolID)->where('id', $id)->firstOrFail();
        $quiz->is_published = !$quiz->is_published;
        $quiz->save();

        return back()->with('success', $quiz->is_published ? 'Quiz imechapishwa.' : 'Quiz imefichwa.')
            ->with('portal_tab', 'quizzes');
    }

    /**
     * Teacher reviews one student attempt (all answers) and can mark written questions.
     */
    public function reviewAttempt($quizId, $attemptId)
    {
        [$school] = $this->guard();
        $quiz = StudentPortalQuiz::with('questions')
            ->where('schoolID', $school->schoolID)
            ->where('id', $quizId)
            ->firstOrFail();

        $attempt = StudentPortalQuizAttempt::where('quiz_id', $quiz->id)
            ->where('id', $attemptId)
            ->where('schoolID', $school->schoolID)
            ->firstOrFail();

        $student = Student::where('schoolID', $school->schoolID)
            ->where('studentID', $attempt->studentID)
            ->first();

        $questions = $quiz->questions->sortBy('sort_order')->values();
        $manualScores = is_array($attempt->manual_scores) ? $attempt->manual_scores : [];
        $feedback = is_array($attempt->teacher_feedback) ? $attempt->teacher_feedback : [];
        $totals = StudentPortalQuizScoring::recomputeWithManual(
            $questions,
            is_array($attempt->answers) ? $attempt->answers : [],
            $manualScores
        );

        $userType = Session::get('user_type');

        return view('Teacher.student_portal_quiz_review', [
            'school' => $school,
            'quiz' => $quiz,
            'attempt' => $attempt,
            'student' => $student,
            'questions' => $questions,
            'manualScores' => $manualScores,
            'feedback' => $feedback,
            'totals' => $totals,
            'user_type' => $userType,
        ]);
    }

    /**
     * Save teacher marks for short answer / essay questions and refresh total score.
     */
    public function markAttempt(Request $request, $quizId, $attemptId)
    {
        [$school] = $this->guard();
        $quiz = StudentPortalQuiz::with('questions')
            ->where('schoolID', $school->schoolID)
            ->where('id', $quizId)
            ->firstOrFail();

        $attempt = StudentPortalQuizAttempt::where('quiz_id', $quiz->id)
            ->where('id', $attemptId)
            ->where('schoolID', $school->schoolID)
            ->firstOrFail();

        $request->validate([
            'marks' => 'nullable|array',
            'feedback' => 'nullable|array',
        ]);

        $marksIn = (array) $request->input('marks', []);
        $feedbackIn = (array) $request->input('feedback', []);
        $manualScores = [];
        $feedback = [];

        foreach ($quiz->questions as $question) {
            $type = $question->question_type ?: 'multiple_choice';
            if (!StudentPortalQuizScoring::isManualType($type)) {
                continue;
            }
            $qid = (string) $question->id;
            $max = (float) ($question->points ?: 1);
            if (array_key_exists($qid, $marksIn) || array_key_exists($question->id, $marksIn)) {
                $raw = $marksIn[$qid] ?? $marksIn[$question->id];
                if ($raw === '' || $raw === null) {
                    continue;
                }
                $pts = is_numeric($raw) ? (float) $raw : 0.0;
                $manualScores[$qid] = max(0.0, min($max, $pts));
            }
            $note = trim((string) ($feedbackIn[$qid] ?? $feedbackIn[$question->id] ?? ''));
            if ($note !== '') {
                $feedback[$qid] = mb_substr($note, 0, 2000);
            }
        }

        $totals = StudentPortalQuizScoring::recomputeWithManual(
            $quiz->questions,
            is_array($attempt->answers) ? $attempt->answers : [],
            $manualScores
        );

        $attempt->update([
            'manual_scores' => $manualScores,
            'teacher_feedback' => $feedback ?: null,
            'score' => $totals['score'],
            'total' => $totals['total'] > 0 ? $totals['total'] : $attempt->total,
            'graded_at' => now(),
            'graded_by' => Session::get('teacherID') ?: Session::get('adminID'),
        ]);

        $msg = $totals['pending'] > 0
            ? 'Alama zimehifadhiwa. Bado kuna maswali '.$totals['pending'].' yanayosubiri alama.'
            : 'Alama zimehifadhiwa. Marking imekamilika.';

        return redirect()
            ->route('student_portal.materials.attempts.review', ['quizId' => $quiz->id, 'attemptId' => $attempt->id])
            ->with('success', $msg);
    }

    public function exportResults(Request $request, $quizId = null)
    {
        [$school] = $this->guard();
        $format = strtolower((string) $request->query('format', 'xlsx'));
        if (!in_array($format, ['xlsx', 'csv'], true)) {
            $format = 'xlsx';
        }

        $quizzesQuery = StudentPortalQuiz::with(['questions', 'attempts'])
            ->where('schoolID', $school->schoolID);
        if ($quizId) {
            $quizzesQuery->where('id', $quizId);
        }
        $quizzes = $quizzesQuery->latest()->get();
        if ($quizzes->isEmpty()) {
            return back()->with('error', 'Quiz not found.')->with('portal_tab', 'results');
        }

        $studentIds = $quizzes->flatMap(fn ($q) => $q->attempts->pluck('studentID'))->unique()->filter();
        $studentsById = $studentIds->isNotEmpty()
            ? Student::where('schoolID', $school->schoolID)->whereIn('studentID', $studentIds)->get()->keyBy('studentID')
            : collect();

        $rows = [[
            'Quiz',
            'Student name',
            'Admission number',
            'Score',
            'Total',
            'Percent',
            'Submitted at',
        ]];

        foreach ($quizzes as $quiz) {
            foreach ($quiz->attempts as $attempt) {
                $st = $studentsById->get($attempt->studentID);
                $name = $st
                    ? trim(($st->first_name ?? '') . ' ' . ($st->middle_name ?? '') . ' ' . ($st->last_name ?? ''))
                    : 'Student #' . $attempt->studentID;
                $pct = $attempt->total > 0 ? round(($attempt->score / $attempt->total) * 100, 1) : 0;
                $rows[] = [
                    $quiz->title,
                    $name,
                    $st->admission_number ?? '',
                    $attempt->score,
                    $attempt->total,
                    $pct,
                    $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i') : '',
                ];
            }
        }

        if (count($rows) < 2) {
            return back()->with('error', 'No results to export yet.')->with('portal_tab', 'results');
        }

        $stamp = now()->format('Ymd_His');
        $baseName = $quizId
            ? 'quiz_results_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $quizzes->first()->title) . '_' . $stamp
            : 'all_quiz_results_' . $stamp;

        if ($format === 'csv') {
            $filename = $baseName . '.csv';
            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
            };

            return response()->streamDownload($callback, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Results');
        $sheet->fromArray($rows, null, 'A1');
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $filename = $baseName . '.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'lph_');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function guard(): array
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!in_array($userType, ['Admin', 'Teacher'], true) || !$schoolID) {
            redirect()->route('login')->with('error', 'Access denied')->throwResponse();
        }

        $school = School::find($schoolID);
        if (!$school || !$school->isStudentPortalEnabled()) {
            redirect()->route('student_portal.materials')->with('error', 'Student portal haijawashwa kwa shule hii.')->throwResponse();
        }

        return [$school];
    }

    private function validatePlacement(Request $request, bool $requireSubject = false): array
    {
        $rules = [
            'classID' => 'required|integer',
            'subclassID' => 'nullable|integer',
            'subjectID' => $requireSubject ? 'required|integer' : 'nullable|integer',
        ];
        $data = $request->validate($rules);

        $placement = [
            'classID' => (int) $data['classID'],
            'subclassID' => !empty($data['subclassID']) ? (int) $data['subclassID'] : null,
            'subjectID' => !empty($data['subjectID']) ? (int) $data['subjectID'] : null,
        ];

        $this->assertTeacherCanPlace(
            $placement['classID'],
            $placement['subclassID'],
            $placement['subjectID'],
            $requireSubject
        );

        return $placement;
    }

    /**
     * Classes / streams / subjects available for portal placement.
     * Teachers only see what they teach (class_subjects + component papers).
     *
     * @return array{classes: \Illuminate\Support\Collection, subclasses: \Illuminate\Support\Collection, subjects: \Illuminate\Support\Collection, subjectClassMap: array<int, list<int>>}
     */
    private function placementOptionsForUser(string $userType, int $schoolID): array
    {
        if ($userType === 'Teacher') {
            $teacherID = (int) Session::get('teacherID');
            $assignments = $teacherID > 0
                ? $this->teacherTeachingAssignments($teacherID)
                : collect();

            $classIds = $assignments->pluck('classID')->unique()->filter()->values();
            $classes = $classIds->isNotEmpty()
                ? ClassModel::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->whereIn('classID', $classIds)
                    ->orderBy('class_name')
                    ->get()
                : collect();

            $allowedSubclassIds = $assignments->pluck('subclassID')->filter()->unique()->values();
            $classesWithAllStreams = $assignments
                ->filter(fn ($row) => empty($row->subclassID))
                ->pluck('classID')
                ->unique()
                ->values();

            $subclasses = $classes->isNotEmpty()
                ? Subclass::whereIn('classID', $classes->pluck('classID'))
                    ->where(function ($q) use ($allowedSubclassIds, $classesWithAllStreams) {
                        if ($allowedSubclassIds->isNotEmpty()) {
                            $q->whereIn('subclassID', $allowedSubclassIds);
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                        if ($classesWithAllStreams->isNotEmpty()) {
                            $q->orWhereIn('classID', $classesWithAllStreams);
                        }
                    })
                    ->orderBy('subclass_name')
                    ->get()
                : collect();

            $subjectIds = $assignments->pluck('subjectID')->unique()->filter()->values();
            $subjects = $subjectIds->isNotEmpty()
                ? SchoolSubject::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->whereIn('subjectID', $subjectIds)
                    ->orderBy('subject_name')
                    ->get()
                : collect();

            $subjectClassMap = [];
            foreach ($assignments as $row) {
                $sid = (int) $row->subjectID;
                $cid = (int) $row->classID;
                if ($sid <= 0 || $cid <= 0) {
                    continue;
                }
                if (!isset($subjectClassMap[$sid])) {
                    $subjectClassMap[$sid] = [];
                }
                if (!in_array($cid, $subjectClassMap[$sid], true)) {
                    $subjectClassMap[$sid][] = $cid;
                }
            }

            return compact('classes', 'subclasses', 'subjects', 'subjectClassMap');
        }

        $classes = ClassModel::where('schoolID', $schoolID)->where('status', 'Active')->orderBy('class_name')->get();
        $subclasses = Subclass::whereIn('classID', $classes->pluck('classID'))->orderBy('subclass_name')->get();
        $subjects = SchoolSubject::where('schoolID', $schoolID)->where('status', 'Active')->orderBy('subject_name')->get();
        $subjectClassMap = [];
        foreach ($subjects as $subject) {
            $subjectClassMap[(int) $subject->subjectID] = $classes->pluck('classID')->map(fn ($id) => (int) $id)->all();
        }

        return compact('classes', 'subclasses', 'subjects', 'subjectClassMap');
    }

    private function teacherTeachingAssignments(int $teacherID)
    {
        return ClassSubject::query()
            ->where('status', 'Active')
            ->where(function ($q) use ($teacherID) {
                $q->where('teacherID', $teacherID)
                    ->orWhereHas('componentTeachers', function ($cq) use ($teacherID) {
                        $cq->where('teacherID', $teacherID);
                    });
            })
            ->get(['classID', 'subclassID', 'subjectID']);
    }

    private function assertTeacherCanPlace(int $classID, ?int $subclassID, ?int $subjectID, bool $requireSubject = false): void
    {
        if (Session::get('user_type') !== 'Teacher') {
            return;
        }

        $teacherID = (int) Session::get('teacherID');
        if ($teacherID <= 0) {
            throw ValidationException::withMessages([
                'classID' => 'Teacher session is missing. Please sign in again.',
            ]);
        }

        if ($requireSubject && !$subjectID) {
            throw ValidationException::withMessages([
                'subjectID' => 'Select the subject you teach for this quiz.',
            ]);
        }

        $query = ClassSubject::query()
            ->where('status', 'Active')
            ->where('classID', $classID)
            ->where(function ($q) use ($teacherID) {
                $q->where('teacherID', $teacherID)
                    ->orWhereHas('componentTeachers', function ($cq) use ($teacherID) {
                        $cq->where('teacherID', $teacherID);
                    });
            });

        if ($subjectID) {
            $query->where('subjectID', $subjectID);
        }

        if ($subclassID) {
            $query->where(function ($q) use ($subclassID) {
                $q->whereNull('subclassID')->orWhere('subclassID', $subclassID);
            });
        }

        if (!$query->exists()) {
            throw ValidationException::withMessages([
                'classID' => 'You can only publish to a class and subject you teach.',
            ]);
        }
    }

    private function rejectBadUpload(Request $request, string $tab)
    {
        // Empty POST when file exceeds post_max_size - PHP drops the body.
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMax = $this->iniBytes(ini_get('post_max_size'));
        if ($contentLength > 0 && empty($request->all()) && empty($_FILES) && $postMax > 0 && $contentLength > $postMax) {
            return back()->with('error',
                'Faili ni kubwa sana. Kikomo cha server (post_max_size) ni ' . ini_get('post_max_size') . '. Punguza faili au ongeza kikomo cha PHP.'
            )->with('portal_tab', $tab);
        }

        if (!$request->hasFile('file') && $request->file('file') === null) {
            $err = $_FILES['file']['error'] ?? null;
            if ($err !== null && (int) $err !== UPLOAD_ERR_NO_FILE) {
                return back()->with('error', $this->uploadErrorMessage((int) $err))->with('portal_tab', $tab)->withInput();
            }

            return null;
        }

        $file = $request->file('file');
        if ($file && !$file->isValid()) {
            return back()->with('error', $this->uploadErrorMessage($file->getError()))->with('portal_tab', $tab)->withInput();
        }

        return null;
    }

    private function uploadErrorMessage(int $code): string
    {
        $uploadMax = ini_get('upload_max_filesize') ?: '2M';
        $postMax = ini_get('post_max_size') ?: '8M';

        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                "Faili ni kubwa sana. Kikomo cha PHP ni {$uploadMax} (post {$postMax}). Tumia faili ndogo au anzisha upya server baada ya kuongeza kikomo.",
            UPLOAD_ERR_PARTIAL => 'Faili ilipakiwa kwa sehemu tu. Jaribu tena.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder ya muda ya upload haipo kwenye server.',
            UPLOAD_ERR_CANT_WRITE => 'Server imeshindwa kuandika faili disk.',
            UPLOAD_ERR_EXTENSION => 'Extension ya PHP imezuia upload.',
            default => "Upload imeshindikana (code {$code}). Kikomo cha server: {$uploadMax}.",
        };
    }

    private function iniBytes(?string $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $num = (float) $value;
        return (int) match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }

    /**
     * @return string|null|false  normalized URL, null if empty, false if invalid
     */
    private function normalizeExternalUrl($raw)
    {
        $url = trim((string) $raw);
        if ($url === '') {
            return null;
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return $url;
    }

    private function storeFile(Request $request, $schoolID): array
    {
        if (!$request->hasFile('file')) {
            return [];
        }

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return [];
        }

        $dir = public_path('uploads/student_portal/' . $schoolID);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Could not create upload folder: ' . $dir);
        }
        $original = $file->getClientOriginalName();
        $name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
        $file->move($dir, $name);

        return [
            'path' => 'uploads/student_portal/' . $schoolID . '/' . $name,
            'name' => $original,
        ];
    }

    private function deleteStoredFile(?string $path): void
    {
        if (!$path) {
            return;
        }
        $full = public_path(ltrim(str_replace('\\', '/', $path), '/'));
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
