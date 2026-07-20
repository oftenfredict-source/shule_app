<?php

namespace App\Http\Controllers;

use App\Models\OnlineAdmission;
use App\Services\OnlineAdmissionFormService;
use App\Services\OnlineAdmissionService;
use App\Services\OnlineAdmissionSpaceService;
use App\Services\OnlineApplicationAuthService;
use App\Services\OnlineApplicationSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnlineApplicationDashboardController extends Controller
{
    public function __construct(
        private OnlineApplicationSchoolService $schoolService,
        private OnlineApplicationAuthService $authService,
        private OnlineAdmissionService $admissionService,
        private OnlineAdmissionFormService $formService,
        private OnlineAdmissionSpaceService $spaceService
    ) {
    }

    public function dashboard()
    {
        $user = auth('online_application')->user();
        $recentApplications = OnlineAdmission::with(['school', 'class', 'academicYear'])
            ->where('online_application_userID', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('online_application.portal.dashboard', compact('user', 'recentApplications'));
    }

    public function myApplications()
    {
        $user = auth('online_application')->user();
        $applications = OnlineAdmission::with(['school', 'class', 'academicYear'])
            ->where('online_application_userID', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('online_application.portal.my-applications', compact('user', 'applications'));
    }

    public function editApplication(int $id)
    {
        $admission = $this->ownedPendingOrFail($id);
        $school = $admission->school;
        $class = $admission->class;

        if (!$school || !$class) {
            return redirect()
                ->route('online_application.portal.applications')
                ->with('error', 'School or class for this application is no longer available.');
        }

        $stats = $this->spaceService->availableSpaces(
            (int) $admission->schoolID,
            (int) $admission->classID,
            (int) $admission->academic_yearID
        );
        $availableSpaces = max(1, $stats);
        $academicYearID = (int) $admission->academic_yearID;
        $academicYearName = $admission->academicYear->year_name
            ?? $admission->academicYear->year
            ?? null;

        $this->formService->ensureDefaults((int) $admission->schoolID, (int) $admission->classID);
        $formSections = $this->formService->activeSectionsWithFields((int) $admission->schoolID, (int) $admission->classID);
        $token = null;
        $editing = true;

        return view('apply', compact(
            'school',
            'class',
            'availableSpaces',
            'token',
            'formSections',
            'academicYearID',
            'academicYearName',
            'admission',
            'editing'
        ));
    }

    public function updateApplication(Request $request, int $id)
    {
        $wantsJson = $request->expectsJson() || $request->ajax();
        $admission = $this->ownedPendingOrFail($id);

        $validation = $this->admissionService->buildValidationRules(
            (int) $admission->schoolID,
            (int) $admission->classID,
            true
        );
        $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

        if ($validator->fails()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        try {
            $this->admissionService->updatePending($admission, $request);
        } catch (\Throwable $e) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully.',
                'redirect' => route('online_application.portal.applications'),
            ]);
        }

        return redirect()
            ->route('online_application.portal.applications')
            ->with('success', 'Application updated successfully.');
    }

    public function deleteApplication(Request $request, int $id)
    {
        $wantsJson = $request->expectsJson() || $request->ajax();
        $admission = $this->ownedPendingOrFail($id);

        try {
            $this->admissionService->deletePending($admission);
        } catch (\Throwable $e) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Application deleted successfully.',
            ]);
        }

        return redirect()
            ->route('online_application.portal.applications')
            ->with('success', 'Application deleted successfully.');
    }

    private function ownedPendingOrFail(int $id): OnlineAdmission
    {
        $user = auth('online_application')->user();
        $admission = OnlineAdmission::with(['school', 'class', 'academicYear'])
            ->where('id', $id)
            ->where('online_application_userID', $user->id)
            ->firstOrFail();

        if ($admission->status !== OnlineAdmission::STATUS_PENDING) {
            abort(403, 'Only pending applications can be changed.');
        }

        return $admission;
    }

    public function applyNow(Request $request)
    {
        $search = $request->get('search', '');
        $page = max(1, (int) $request->get('page', 1));
        $result = $this->schoolService->paginatedSchools($search ?: null, $page, 10);

        return view('online_application.portal.apply', [
            'user' => auth('online_application')->user(),
            'schools' => $result['schools'],
            'search' => $search,
            'hasMore' => $result['hasMore'],
            'nextPage' => $result['nextPage'],
        ]);
    }

    public function account()
    {
        $user = auth('online_application')->user();

        return view('online_application.portal.account', compact('user'));
    }

    public function showChangePassword()
    {
        $user = auth('online_application')->user();

        return view('online_application.portal.change-password', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = auth('online_application')->user();

        try {
            $this->authService->changePassword(
                $user,
                $request->input('current_password'),
                $request->input('password')
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }

        return back()->with('success', 'Password updated successfully.');
    }
}
