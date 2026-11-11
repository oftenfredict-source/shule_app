<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Student;
use App\Models\StudentPortalBook;
use App\Models\StudentPortalNote;
use App\Models\StudentPortalQuiz;
use App\Models\StudentPortalQuizAttempt;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StudentPortalController extends Controller
{
    public function showLogin()
    {
        if ($this->currentStudent()) {
            return redirect()->route('student_portal.home');
        }

        return response()
            ->view('StudentPortal.login')
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'admission_number' => 'required|string|max:80',
        ]);

        $admission = trim($data['admission_number']);
        $students = Student::query()
            ->where('status', 'Active')
            ->whereRaw('LOWER(admission_number) = ?', [mb_strtolower($admission)])
            ->get();

        $matches = $students->filter(function ($student) {
            $school = School::find($student->schoolID);

            return $school
                && $school->isStudentPortalEnabled()
                && $school->status === 'Active'
                && ($school->login_status === null || (int) $school->login_status !== 0);
        })->values();

        if ($matches->isEmpty()) {
            return back()->withInput()->with('error', 'Admission number not found, or this school does not have a student portal.');
        }

        if ($matches->count() > 1) {
            return back()->withInput()->with('error', 'This admission number matches more than one student. Please contact the school.');
        }

        $student = $matches->first();
        Session::put('student_portal_studentID', $student->studentID);
        Session::put('student_portal_schoolID', $student->schoolID);

        return redirect()->route('student_portal.home');
    }

    public function logout()
    {
        Session::forget(['student_portal_studentID', 'student_portal_schoolID']);

        return redirect()->route('student_portal.login');
    }

    public function home()
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $scope = $this->scopeIds($student);

        return view('StudentPortal.home', [
            'student' => $student,
            'school' => $school,
            'classLabel' => $classLabel,
            'noteCount' => $this->notesQuery($school->schoolID, $scope)->count(),
            'bookCount' => $this->booksQuery($school->schoolID, $scope)->count(),
            'quizCount' => $this->quizzesQuery($school->schoolID, $scope)->count(),
        ]);
    }

    public function notes()
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $notes = $this->notesQuery($school->schoolID, $this->scopeIds($student))->latest()->get();

        return view('StudentPortal.notes', compact('student', 'school', 'classLabel', 'notes'));
    }

    public function note($id)
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $note = $this->notesQuery($school->schoolID, $this->scopeIds($student))->where('id', $id)->firstOrFail();

        return view('StudentPortal.note', compact('student', 'school', 'classLabel', 'note'));
    }

    public function downloadNote($id)
    {
        [$student, $school] = array_slice($this->requireStudent(), 0, 2);
        $note = $this->notesQuery($school->schoolID, $this->scopeIds($student))->where('id', $id)->firstOrFail();

        return $this->downloadPortalFile($note->file_path, $note->original_name);
    }

    public function books()
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $books = $this->booksQuery($school->schoolID, $this->scopeIds($student))->latest()->get();

        return view('StudentPortal.books', compact('student', 'school', 'classLabel', 'books'));
    }

    public function book($id)
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $book = $this->booksQuery($school->schoolID, $this->scopeIds($student))->where('id', $id)->firstOrFail();

        return view('StudentPortal.book', compact('student', 'school', 'classLabel', 'book'));
    }

    public function downloadBook($id)
    {
        [$student, $school] = array_slice($this->requireStudent(), 0, 2);
        $book = $this->booksQuery($school->schoolID, $this->scopeIds($student))->where('id', $id)->firstOrFail();

        return $this->downloadPortalFile($book->file_path, $book->original_name);
    }

    public function quizzes()
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $quizzes = $this->quizzesQuery($school->schoolID, $this->scopeIds($student))->latest()->get();
        $attempts = StudentPortalQuizAttempt::where('studentID', $student->studentID)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()
            ->keyBy('quiz_id');

        return view('StudentPortal.quizzes', compact('student', 'school', 'classLabel', 'quizzes', 'attempts'));
    }

    public function quiz($id)
    {
        [$student, $school, $classLabel] = $this->requireStudent();
        $quiz = $this->quizzesQuery($school->schoolID, $this->scopeIds($student))->where('id', $id)->firstOrFail();
        $quiz->load('questions');
        $attempt = StudentPortalQuizAttempt::where('quiz_id', $quiz->id)->where('studentID', $student->studentID)->first();

        return view('StudentPortal.quiz', compact('student', 'school', 'classLabel', 'quiz', 'attempt'));
    }

    public function submitQuiz(Request $request, $id)
    {
        [$student, $school] = array_slice($this->requireStudent(), 0, 2);
        $quiz = $this->quizzesQuery($school->schoolID, $this->scopeIds($student))->where('id', $id)->firstOrFail();
        $quiz->load('questions');

        $existing = StudentPortalQuizAttempt::where('quiz_id', $quiz->id)->where('studentID', $student->studentID)->first();
        if ($existing) {
            return redirect()->route('student_portal.quiz', $quiz->id)->with('error', 'You have already taken this quiz.');
        }

        if ($quiz->windowStatus() === 'upcoming') {
            return redirect()->route('student_portal.quiz', $quiz->id)->with('error', 'This quiz is not open yet.');
        }

        if ($quiz->closes_at && now()->gt($quiz->closes_at->copy()->addSeconds(20))) {
            return redirect()->route('student_portal.quiz', $quiz->id)->with('error', 'Time is up. This quiz is closed.');
        }

        $answers = $request->input('answers', []);
        if (!is_array($answers)) {
            $answers = [];
        }

        $graded = \App\Support\StudentPortalQuizScoring::grade($quiz->questions, $answers);

        StudentPortalQuizAttempt::create([
            'quiz_id' => $quiz->id,
            'studentID' => $student->studentID,
            'schoolID' => $school->schoolID,
            'score' => $graded['score'],
            'total' => $graded['total'] > 0 ? $graded['total'] : $quiz->questions->count(),
            'answers' => $graded['stored'],
            'submitted_at' => now(),
        ]);

        $msg = 'Quiz submitted.';
        if (($graded['manual_pending'] ?? 0) > 0) {
            $msg .= ' Written answers were saved for teacher review.';
        }

        return redirect()->route('student_portal.quiz', $quiz->id)->with('success', $msg);
    }

    private function requireStudent(): array
    {
        $context = $this->currentStudent();
        if (!$context) {
            redirect()->route('student_portal.login')->with('error', 'Please sign in with your admission number.')->throwResponse();
        }

        return $context;
    }

    private function currentStudent(): ?array
    {
        $studentID = Session::get('student_portal_studentID');
        $schoolID = Session::get('student_portal_schoolID');
        if (!$studentID || !$schoolID) {
            return null;
        }

        $student = Student::where('studentID', $studentID)->where('schoolID', $schoolID)->where('status', 'Active')->first();
        $school = School::find($schoolID);
        if (!$student || !$school || !$school->isStudentPortalEnabled()) {
            Session::forget(['student_portal_studentID', 'student_portal_schoolID']);

            return null;
        }

        $enrollment = app(StudentEnrollmentService::class)->currentEnrollment((int) $student->studentID, (int) $school->schoolID);
        $className = $enrollment?->classModel?->class_name;
        $subclassName = $enrollment?->subclass?->subclass_name;
        $classLabel = trim(($className ?? '') . ' ' . ($subclassName ?? ''));

        return [$student, $school, $classLabel !== '' ? $classLabel : '-'];
    }

    private function scopeIds(Student $student): array
    {
        $enrollment = app(StudentEnrollmentService::class)->currentEnrollment((int) $student->studentID, (int) $student->schoolID);

        return [
            'classID' => $enrollment->classID ?? null,
            'subclassID' => $enrollment->subclassID ?? null,
        ];
    }

    private function applyScope($query, array $scope)
    {
        $classID = $scope['classID'] ?? null;
        $subclassID = $scope['subclassID'] ?? null;

        return $query->where('is_published', true)->where(function ($q) use ($classID, $subclassID) {
            $q->whereNull('classID');
            if ($classID) {
                $q->orWhere(function ($inner) use ($classID, $subclassID) {
                    $inner->where('classID', $classID)->where(function ($stream) use ($subclassID) {
                        $stream->whereNull('subclassID');
                        if ($subclassID) {
                            $stream->orWhere('subclassID', $subclassID);
                        }
                    });
                });
            }
        });
    }

    private function notesQuery(int $schoolID, array $scope)
    {
        return $this->applyScope(StudentPortalNote::where('schoolID', $schoolID), $scope);
    }

    private function booksQuery(int $schoolID, array $scope)
    {
        return $this->applyScope(StudentPortalBook::where('schoolID', $schoolID), $scope);
    }

    private function quizzesQuery(int $schoolID, array $scope)
    {
        return $this->applyScope(StudentPortalQuiz::where('schoolID', $schoolID), $scope);
    }

    /**
     * Stream a portal attachment through Laravel so downloads work even when
     * static /uploads URLs 404 under public_html / subdirectory deploys.
     */
    private function downloadPortalFile(?string $path, ?string $originalName)
    {
        $absolute = $this->resolvePortalFilePath($path);
        if (!$absolute) {
            abort(404, 'File not found.');
        }

        $downloadName = $originalName ?: basename($absolute);

        return response()->download($absolute, $downloadName, [
            'Content-Type' => mime_content_type($absolute) ?: 'application/octet-stream',
        ]);
    }

    private function resolvePortalFilePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $candidates = [
            public_path($normalized),
            base_path('public/' . $normalized),
            base_path($normalized),
        ];

        // Absolute path already stored in DB (legacy).
        if (preg_match('~^[A-Za-z]:[\\\\/]|^/~', $path) === 1) {
            array_unshift($candidates, $path);
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
