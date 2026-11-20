<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CharacterAssessmentController;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hub for Main → Character Assessment (web: /teacher/character-assessment).
 * Save stays as bridge; students/history are cleaned JSON.
 */
class TeacherCharacterAssessmentMobileApiController extends Controller
{
    /** GET /character-assessment */
    public function index()
    {
        $result = $this->callWeb(CharacterAssessmentController::class, 'teacher_index');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Character Assessment is not available.');
        }

        $d = $result->getData();
        $categories = collect($d['categories'] ?? []);
        $choices = collect($d['globalChoices'] ?? []);
        $assignments = collect($d['assignments'] ?? []);
        $subclasses = collect($d['subclasses'] ?? []);

        $shapedAssignments = $assignments->map(function ($a) use ($categories) {
            $selected = collect($a->selected_categories ?? [])->map(function ($cfg) use ($categories) {
                $categoryID = is_array($cfg) || is_object($cfg)
                    ? (int) (data_get($cfg, 'categoryID') ?? data_get($cfg, 'id') ?? 0)
                    : (int) $cfg;
                $number = is_array($cfg) || is_object($cfg)
                    ? (data_get($cfg, 'number') ?? null)
                    : null;
                $cat = $categories->firstWhere('categoryID', $categoryID);

                return [
                    'categoryID' => $categoryID,
                    'number' => $number !== null ? (int) $number : null,
                    'name' => $cat->name ?? null,
                    'type' => $cat->type ?? null,
                ];
            })->values();

            $tt = $a->timetable;

            return [
                'assignmentID' => (int) $a->assignmentID,
                'timetableID' => (int) ($a->timetableID ?? $tt->timetableID ?? 0),
                'classID' => (int) ($a->classID ?? 0),
                'subclassID' => $a->subclassID ? (int) $a->subclassID : null,
                'class_name' => $a->class->class_name ?? null,
                'subclass_name' => $a->subclass->subclass_name ?? null,
                'is_all_groups' => empty($a->subclassID),
                'selected_categories' => $selected,
                'timetable' => $tt ? [
                    'timetableID' => (int) $tt->timetableID,
                    'start_date' => $this->fmtDate($tt->start_date ?? null),
                    'end_date' => $this->fmtDate($tt->end_date ?? null),
                    'year' => $tt->year ?? null,
                    'term' => $tt->term ?? null,
                ] : null,
            ];
        })->values();

        $mainClasses = $shapedAssignments
            ->unique('classID')
            ->map(fn ($a) => [
                'classID' => $a['classID'],
                'class_name' => $a['class_name'],
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Character Assessment',
                'period_status' => $d['periodStatus'] ?? 'none',
                'can_assess' => ($d['periodStatus'] ?? 'none') !== 'none' && $shapedAssignments->isNotEmpty(),
                'assignments' => $shapedAssignments,
                'main_classes' => $mainClasses,
                'categories' => $categories->map(fn ($c) => [
                    'categoryID' => (int) $c->categoryID,
                    'name' => $c->name ?? null,
                    'type' => $c->type ?? null,
                ])->values(),
                'choices' => $choices->map(fn ($c) => [
                    'choiceID' => (int) $c->choiceID,
                    'choice_name' => $c->choice_name ?? null,
                    'weight' => (float) ($c->weight ?? 0),
                ])->values(),
                'subclasses' => $subclasses->map(fn ($s) => [
                    'subclassID' => (int) $s->subclassID,
                    'classID' => (int) ($s->classID ?? 0),
                    'subclass_name' => $s->subclass_name ?? null,
                ])->values(),
                'actions' => [
                    'students' => 'GET /character-assessment/students?classID=&subclassID=&timetableID=',
                    'history' => 'GET /character-assessment/history?studentID=&timetableID=',
                    'save' => 'POST /character-assessment',
                ],
            ],
        ]);
    }

    /** GET /character-assessment/students */
    public function students(Request $request)
    {
        $result = $this->callWeb(CharacterAssessmentController::class, 'get_assessment_students');
        if (!$result instanceof JsonResponse) {
            return $this->forbiddenOrPassthrough($result, 'Failed to load students.');
        }

        $payload = $result->getData(true);
        if (!($payload['success'] ?? false)) {
            return $result;
        }

        $students = collect($payload['students'] ?? [])->map(function ($s) {
            $s = is_array($s) ? $s : (method_exists($s, 'toArray') ? $s->toArray() : (array) $s);
            $assessments = collect($s['character_assessments'] ?? $s['characterAssessments'] ?? [])
                ->map(fn ($r) => [
                    'categoryID' => (int) ($r['categoryID'] ?? 0),
                    'choiceID' => isset($r['choiceID']) && $r['choiceID'] ? (int) $r['choiceID'] : null,
                    'description' => $r['description'] ?? null,
                ])->values();

            $name = trim(implode(' ', array_filter([
                $s['first_name'] ?? null,
                $s['middle_name'] ?? null,
                $s['last_name'] ?? null,
            ])));

            return [
                'studentID' => (int) ($s['studentID'] ?? 0),
                'full_name' => $name,
                'admission_number' => $s['admission_number'] ?? null,
                'enrollmentID' => isset($s['enrollmentID']) ? (int) $s['enrollmentID'] : null,
                'enrollment_status' => $s['enrollment_status'] ?? 'Active',
                'can_assess' => (bool) ($s['can_assess'] ?? (($s['enrollment_status'] ?? 'Active') === 'Active')),
                'subclass_name' => $s['subclass_name'] ?? null,
                'assessments' => $assessments,
                'is_complete' => $assessments->isNotEmpty(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'students' => $students,
            'active_enrollment_count' => (int) ($payload['active_enrollment_count'] ?? 0),
        ]);
    }

    /** GET /character-assessment/history */
    public function history(Request $request)
    {
        $result = $this->callWeb(CharacterAssessmentController::class, 'get_student_assessment_history');
        if (!$result instanceof JsonResponse) {
            return $this->forbiddenOrPassthrough($result, 'Failed to load history.');
        }

        $payload = $result->getData(true);
        if (!($payload['success'] ?? false)) {
            return $result;
        }

        $history = collect($payload['history'] ?? [])->map(function ($h) {
            $h = is_array($h) ? $h : (method_exists($h, 'toArray') ? $h->toArray() : (array) $h);

            return [
                'categoryID' => (int) ($h['categoryID'] ?? 0),
                'category_name' => $h['category']['name'] ?? null,
                'category_type' => $h['category']['type'] ?? null,
                'choiceID' => isset($h['choiceID']) && $h['choiceID'] ? (int) $h['choiceID'] : null,
                'choice_name' => $h['choice']['choice_name'] ?? null,
                'choice_weight' => isset($h['choice']['weight']) ? (float) $h['choice']['weight'] : null,
                'description' => $h['description'] ?? null,
            ];
        })->values();

        return response()->json(['success' => true, 'history' => $history]);
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
