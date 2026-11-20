<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WatchmanController;
use App\Models\School;
use App\Models\Watchman;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile hub for Watchman gate visitors (web: /watchman/visitors).
 */
class WatchmanMobileApiController extends Controller
{
    /** GET /watchman/profile */
    public function profile(Request $request)
    {
        $watchman = $request->attributes->get('mobile_watchman');
        $school = School::find($watchman->schoolID ?? null);

        return response()->json([
            'success' => true,
            'data' => [
                'watchman' => $this->watchmanPayload($watchman),
                'school' => $school ? [
                    'schoolID' => (int) $school->schoolID,
                    'school_name' => $school->school_name,
                ] : null,
                'schoolID' => (int) ($watchman->schoolID ?? 0),
            ],
        ]);
    }

    /**
     * GET /watchman/visitors — hub for the gate registration screen.
     * Includes today's list so the app can open one screen like the website.
     */
    public function hub(Request $request)
    {
        $watchman = $request->attributes->get('mobile_watchman');
        $today = $this->callWeb(WatchmanController::class, 'todayVisitors');
        if (!$today instanceof JsonResponse) {
            return response()->json(['success' => false, 'message' => 'Failed to load visitors.'], 500);
        }

        $payload = $today->getData(true);
        if (!($payload['success'] ?? false)) {
            return $today;
        }

        $visitors = collect($payload['data'] ?? []);
        $onSite = $visitors->filter(fn ($v) => !empty($v['signedInAt']) && empty($v['signedOutAt']))->count();
        $completed = $visitors->filter(fn ($v) => !empty($v['signedInAt']) && !empty($v['signedOutAt']))->count();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Usajili wa Wageni wa Shule',
                'screen' => 'visitors',
                'date' => Carbon::today()->toDateString(),
                'watchman' => $this->watchmanPayload($watchman),
                'summary' => [
                    'total_today' => $visitors->count(),
                    'on_site' => $onSite,
                    'completed' => $completed,
                ],
                'visitors' => $visitors->values(),
                'fields' => [
                    ['key' => 'name', 'label' => 'Jina', 'required' => true],
                    ['key' => 'contact', 'label' => 'Simu / Email', 'required' => false, 'default_prefix' => '255'],
                    ['key' => 'occupation', 'label' => 'Kazi', 'required' => false],
                    ['key' => 'reason', 'label' => 'Lengo', 'required' => false],
                    ['key' => 'office_visited', 'label' => 'Ofisi', 'required' => false],
                    ['key' => 'signature', 'label' => 'Saini', 'required' => false, 'type' => 'signature_png_data_url'],
                ],
                'actions' => [
                    'today' => 'GET /watchman/visitors/today',
                    'search' => 'GET /watchman/visitors/search?q=',
                    'store' => 'POST /watchman/visitors',
                    'sign_in' => 'POST /watchman/visitors/sign-in',
                    'sign_out' => 'POST /watchman/visitors/sign-out',
                ],
            ],
        ]);
    }

    /** GET /watchman/visitors/today */
    public function today()
    {
        return $this->passthrough(WatchmanController::class, 'todayVisitors');
    }

    /** GET /watchman/visitors/search?q= */
    public function search(Request $request)
    {
        return $this->passthrough(AdminController::class, 'searchSchoolVisitors', [
            'request' => $request,
        ]);
    }

    /**
     * POST /watchman/visitors
     *
     * Accepts either website parallel arrays:
     *   name[], contact[], occupation[], reason[], office_visited[], signature[]
     * or a mobile-friendly list:
     *   visitors: [{ name, contact, occupation, reason, office_visited, signature }, ...]
     */
    public function store(Request $request)
    {
        $this->normalizeVisitorArrays($request);

        return $this->passthrough(WatchmanController::class, 'storeVisitors', [
            'request' => $request,
        ]);
    }

    /** POST /watchman/visitors/sign-in  body: visitorID, office_visited? */
    public function signIn(Request $request)
    {
        return $this->passthrough(AdminController::class, 'signInSchoolVisitor', [
            'request' => $request,
        ]);
    }

    /** POST /watchman/visitors/sign-out  body: visitorID */
    public function signOut(Request $request)
    {
        return $this->passthrough(AdminController::class, 'signOutSchoolVisitor', [
            'request' => $request,
        ]);
    }

    public function watchmanPayload(?Watchman $watchman): ?array
    {
        if (!$watchman) {
            return null;
        }

        return [
            'id' => (int) $watchman->id,
            'first_name' => $watchman->first_name,
            'last_name' => $watchman->last_name,
            'full_name' => trim(($watchman->first_name ?? '') . ' ' . ($watchman->last_name ?? '')),
            'phone_number' => $watchman->phone_number,
            'email' => $watchman->email,
            'status' => $watchman->status,
            'schoolID' => (int) $watchman->schoolID,
        ];
    }

    private function normalizeVisitorArrays(Request $request): void
    {
        $list = $request->input('visitors');
        if (!is_array($list) || empty($list)) {
            return;
        }

        $names = [];
        $contacts = [];
        $occupations = [];
        $reasons = [];
        $offices = [];
        $signatures = [];

        foreach ($list as $row) {
            if (!is_array($row)) {
                continue;
            }
            $names[] = $row['name'] ?? '';
            $contacts[] = $row['contact'] ?? null;
            $occupations[] = $row['occupation'] ?? null;
            $reasons[] = $row['reason'] ?? null;
            $offices[] = $row['office_visited'] ?? ($row['officeVisited'] ?? null);
            $signatures[] = $row['signature'] ?? null;
        }

        $request->merge([
            'name' => $names,
            'contact' => $contacts,
            'occupation' => $occupations,
            'reason' => $reasons,
            'office_visited' => $offices,
            'signature' => $signatures,
        ]);
    }

    private function passthrough(string $class, string $method, array $params = [])
    {
        $result = $this->callWeb($class, $method, $params);
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json(['success' => false, 'message' => 'Unexpected response.'], 500);
    }

    private function callWeb(string $class, string $method, array $params = [])
    {
        try {
            return app()->call([app($class), $method], $params);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }
}
