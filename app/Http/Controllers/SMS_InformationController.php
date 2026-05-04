<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\ParentModel;
use App\Models\OtherStaff;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\PDF;

class SMS_InformationController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    public function sms_notification()
    {
      $user_type = Session::get('user_type');
      $schoolID = Session::get('schoolID');
        if (!$user_type || !$schoolID) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get school info
        $school = School::find($schoolID);
        
        // Get classes for dropdown
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        return view('Admin.manage_sms_updates', compact('school', 'classes', 'user_type'));
    }

    /**
     * Get all parents in school
     */
    public function get_all_parents()
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            // Optimized query: Only select necessary fields and avoid eager loading
            $parents = ParentModel::where('schoolID', $schoolID)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereHas('students', function($q) {
                    $q->where('status', 'Active');
                })
                ->select('parentID', 'first_name', 'middle_name', 'last_name', 'phone')
                ->with(['students' => function($q) {
                    $q->where('status', 'Active')->select('studentID', 'parentID', 'first_name', 'last_name');
                }])
                ->get();

            // Map parents to add student info
            $parents->transform(function ($parent) {
                $studentName = $parent->students->first() ? $parent->students->first()->first_name . ' ' . $parent->students->first()->last_name : 'Student';
                $parent->display_name = 'Parent of ' . trim($studentName);
                unset($parent->students); // Clean up payload
                return $parent;
            });

            $count = $parents->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'parents' => $parents
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all parents: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parents by class
     */
    public function get_parents_by_class(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $classID = $request->input('classID');
            if (!$classID) {
                return response()->json(['success' => false, 'message' => 'Class ID is required'], 400);
            }

            // Get all subclasses for this class
            $subclassIDs = Subclass::where('classID', $classID)->pluck('subclassID');

            // Optimized query: Only select necessary fields and avoid eager loading
            $parents = ParentModel::where('schoolID', $schoolID)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereHas('students', function($q) use ($subclassIDs) {
                    $q->whereIn('subclassID', $subclassIDs)
                      ->where('status', 'Active');
                })
                ->select('parentID', 'first_name', 'middle_name', 'last_name', 'phone')
                ->with(['students' => function($q) use ($subclassIDs) {
                    $q->whereIn('subclassID', $subclassIDs)->where('status', 'Active')->select('studentID', 'parentID', 'first_name', 'last_name');
                }])
                ->get()
                ->unique('parentID');

            // Map parents to add student info
            $parents = $parents->values();
            $parents->transform(function ($parent) {
                $studentName = $parent->students->first() ? $parent->students->first()->first_name . ' ' . $parent->students->first()->last_name : 'Student';
                $parent->display_name = 'Parent of ' . trim($studentName);
                unset($parent->students); // Clean up payload
                return $parent;
            });

            $count = $parents->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'parents' => $parents
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting parents by class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all teachers
     */
    public function get_all_teachers()
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $teachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->select('id', 'first_name', 'middle_name', 'last_name', 'phone_number')
                ->get();

            $count = $teachers->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'teachers' => $teachers
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all teachers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all staff
     */
    public function get_all_staff()
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $staff = OtherStaff::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->select('id', 'first_name', 'middle_name', 'last_name', 'phone_number')
                ->get();

            $count = $staff->count();

            return response()->json([
                'success' => true,
                'count' => $count,
                'staff' => $staff
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting all staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parent by student
     */
    public function get_parent_by_student(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $studentID = $request->input('studentID');
            if (!$studentID) {
                return response()->json(['success' => false, 'message' => 'Student ID is required'], 400);
            }

            $student = Student::where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('parent')
                ->first();

            if (!$student || !$student->parent) {
                return response()->json(['success' => false, 'message' => 'Active student or parent not found'], 404);
            }

            $parent = $student->parent;
            if (!$parent->phone || $parent->phone == '') {
                return response()->json(['success' => false, 'message' => 'Parent phone number not available'], 400);
            }

            return response()->json([
                'success' => true,
                'parent' => $parent,
                'student' => $student
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting parent by student: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Search students for parent selection
     */
    public function search_students(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $search = $request->input('search', '');
            
            $query = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['parent', 'subclass.class']);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_number', 'like', "%{$search}%");
                });
            }

            $students = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching students: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Search teachers for selection
     */
    public function search_teachers(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $search = $request->input('search', '');

            $query = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            $teachers = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'teachers' => $teachers
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching teachers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Search staff for selection
     */
    public function search_staff(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $search = $request->input('search', '');

            $query = OtherStaff::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            $staff = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'staff' => $staff
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send SMS to recipients
     */
    public function send_sms(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|min:1',
                'recipient_type' => 'required|in:all_parents,class_parents,all_parents_teachers,all_teachers,specific_parent,specific_teacher,all_staff,specific_staff'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
            }

            $school = School::find($schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 404);
            }

            $message = $request->input('message');
            $recipientType = $request->input('recipient_type');
            $selectedIds = $request->input('selected_ids'); // Optional array of IDs
            if ($selectedIds && !is_array($selectedIds)) {
                $selectedIds = explode(',', $selectedIds);
            }
            
            // Prepend school name to message
            $fullMessage = $school->school_name . '. ' . $message;

            $recipients = [];
            $results = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'details' => []
            ];

            // Get recipients based on type
            switch ($recipientType) {
                case 'all_parents':
                    // Get parents who have at least one active student
                    $parentsQuery = ParentModel::where('schoolID', $schoolID)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->whereHas('students', function($q) {
                            $q->where('status', 'Active');
                        })
                        ->with(['students' => function($q) {
                            $q->where('status', 'Active')->select('studentID', 'parentID', 'first_name', 'last_name');
                        }]);
                    
                    if (!empty($selectedIds)) {
                        $parentsQuery->whereIn('parentID', $selectedIds);
                    }

                    $parents = $parentsQuery->get();
                    foreach ($parents as $parent) {
                        $studentName = $parent->students->first() ? $parent->students->first()->first_name . ' ' . $parent->students->first()->last_name : 'Student';
                        $recipients[] = [
                            'type' => 'parent',
                            'phone' => $parent->phone,
                            'name' => 'Parent of ' . trim($studentName),
                            'id' => $parent->parentID
                        ];
                    }
                    break;

                case 'class_parents':
                    $classID = $request->input('classID');
                    if (!$classID) {
                        return response()->json(['success' => false, 'message' => 'Class ID is required'], 400);
                    }
                    $subclassIDs = Subclass::where('classID', $classID)->pluck('subclassID');
                    // Get parents who have at least one active student in these subclasses
                    $parentsQuery = ParentModel::where('schoolID', $schoolID)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->whereHas('students', function($q) use ($subclassIDs) {
                            $q->whereIn('subclassID', $subclassIDs)
                              ->where('status', 'Active');
                        })
                        ->with(['students' => function($q) use ($subclassIDs) {
                            $q->whereIn('subclassID', $subclassIDs)->where('status', 'Active')->select('studentID', 'parentID', 'first_name', 'last_name');
                        }]);
                    
                    if (!empty($selectedIds)) {
                        $parentsQuery->whereIn('parentID', $selectedIds);
                    }

                    $parents = $parentsQuery->get()->unique('parentID');
                    foreach ($parents as $parent) {
                        $studentName = $parent->students->first() ? $parent->students->first()->first_name . ' ' . $parent->students->first()->last_name : 'Student';
                        $recipients[] = [
                            'type' => 'parent',
                            'phone' => $parent->phone,
                            'name' => 'Parent of ' . trim($studentName),
                            'id' => $parent->parentID
                        ];
                    }
                    break;

                case 'all_parents_teachers':
                    // Get parents
                    $parentsQuery = ParentModel::where('schoolID', $schoolID)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->whereHas('students', function($q) {
                            $q->where('status', 'Active');
                        })
                        ->with(['students' => function($q) {
                            $q->where('status', 'Active')->select('studentID', 'parentID', 'first_name', 'last_name');
                        }]);
                    
                    if (!empty($selectedIds)) {
                        $parentsQuery->whereIn('parentID', $selectedIds);
                    }

                    $parents = $parentsQuery->get();
                    foreach ($parents as $parent) {
                        $studentName = $parent->students->first() ? $parent->students->first()->first_name . ' ' . $parent->students->first()->last_name : 'Student';
                        $recipients[] = [
                            'type' => 'parent',
                            'phone' => $parent->phone,
                            'name' => 'Parent of ' . trim($studentName),
                            'id' => $parent->parentID
                        ];
                    }

                    // Get teachers
                    $teachersQuery = Teacher::where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '');
                    
                    if (!empty($selectedIds)) {
                        $teachersQuery->whereIn('id', $selectedIds);
                    }

                    $teachers = $teachersQuery->get();
                    foreach ($teachers as $teacher) {
                        $recipients[] = [
                            'type' => 'teacher',
                            'phone' => $teacher->phone_number,
                            'name' => $teacher->first_name . ' ' . ($teacher->last_name ?? ''),
                            'id' => $teacher->id
                        ];
                    }
                    break;

                case 'all_teachers':
                    $teachersQuery = Teacher::where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '');
                    
                    if (!empty($selectedIds)) {
                        $teachersQuery->whereIn('id', $selectedIds);
                    }

                    $teachers = $teachersQuery->get();
                    foreach ($teachers as $teacher) {
                        $recipients[] = [
                            'type' => 'teacher',
                            'phone' => $teacher->phone_number,
                            'name' => $teacher->first_name . ' ' . ($teacher->last_name ?? ''),
                            'id' => $teacher->id
                        ];
                    }
                    break;

                case 'specific_parent':
                    $studentID = $request->input('studentID');
                    if (!$studentID) {
                        return response()->json(['success' => false, 'message' => 'Student ID is required'], 400);
                    }
                    // Get active student
                    $student = Student::where('studentID', $studentID)
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->with('parent')
                        ->first();
                    if (!$student || !$student->parent || !$student->parent->phone) {
                        return response()->json(['success' => false, 'message' => 'Active student or parent not found or phone number not available'], 404);
                    }
                    $recipients[] = [
                        'type' => 'parent',
                        'phone' => $student->parent->phone,
                        'name' => 'Parent of ' . $student->first_name . ' ' . ($student->last_name ?? ''),
                        'id' => $student->parent->parentID
                    ];
                    break;

                case 'specific_teacher':
                    $teacherID = $request->input('teacherID');
                    if (!$teacherID) {
                        return response()->json(['success' => false, 'message' => 'Teacher ID is required'], 400);
                    }
                    $teacher = Teacher::where('id', $teacherID)
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->first();
                    if (!$teacher) {
                        return response()->json(['success' => false, 'message' => 'Active teacher not found or phone number not available'], 404);
                    }
                    $recipients[] = [
                        'type' => 'teacher',
                        'phone' => $teacher->phone_number,
                        'name' => $teacher->first_name . ' ' . ($teacher->last_name ?? ''),
                        'id' => $teacher->id
                    ];
                    break;

                case 'all_staff':
                    $staffQuery = OtherStaff::where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '');
                    
                    if (!empty($selectedIds)) {
                        $staffQuery->whereIn('id', $selectedIds);
                    }

                    $staff = $staffQuery->get();
                    foreach ($staff as $s) {
                        $recipients[] = [
                            'type' => 'staff',
                            'phone' => $s->phone_number,
                            'name' => $s->first_name . ' ' . ($s->last_name ?? ''),
                            'id' => $s->id
                        ];
                    }
                    break;

                case 'specific_staff':
                    $staffID = $request->input('staffID');
                    if (!$staffID) {
                        return response()->json(['success' => false, 'message' => 'Staff ID is required'], 400);
                    }
                    $staff = OtherStaff::where('id', $staffID)
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Active')
                        ->whereNotNull('phone_number')
                        ->where('phone_number', '!=', '')
                        ->first();
                    if (!$staff) {
                        return response()->json(['success' => false, 'message' => 'Active staff not found or phone number not available'], 404);
                    }
                    $recipients[] = [
                        'type' => 'staff',
                        'phone' => $staff->phone_number,
                        'name' => $staff->first_name . ' ' . ($staff->last_name ?? ''),
                        'id' => $staff->id
                    ];
                    break;
            }

            $results['total'] = count($recipients);

            // If sync is requested, don't use fastcgi_finish_request
            $isSync = $request->input('is_sync', false);

            if (!$isSync && function_exists('fastcgi_finish_request')) {
                $response = response()->json([
                    'success' => true,
                    'status' => 'queued',
                    'message' => 'SMS sending started',
                    'results' => $results
                ]);
                $response->send();
                fastcgi_finish_request();
            }

            $this->sendSmsBatch($recipients, $fullMessage, $results, $recipientType);

            return response()->json([
                'success' => true,
                'status' => 'completed',
                'message' => 'SMS process finished',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function sendSmsBatch(array $recipients, string $fullMessage, array &$results, string $messageType = 'general'): void
    {
        $schoolID   = Session::get('schoolID');
        $sentDate   = now()->toDateString();
        $sentTime   = now()->toTimeString();

        // Determine who sent the SMS
        $sentByName = Session::get('name', 'System');
        $sentByRole = Session::get('role', 'admin');

        $batchSize = 50;
        $batches   = array_chunk($recipients, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $recipient) {
                try {
                    $smsResult = $this->smsService->sendSms($recipient['phone'], $fullMessage);

                    $status    = $smsResult['success'] ? 'PENDING' : 'FAILED';
                    $messageId = $smsResult['message_id'] ?? null;

                    if ($smsResult['success']) {
                        $results['success']++;
                        $results['details'][] = [
                            'phone'  => $recipient['phone'],
                            'name'   => $recipient['name'],
                            'status' => 'success',
                            'message'=> 'SMS sent successfully'
                        ];
                    } else {
                        $results['failed']++;
                        $results['details'][] = [
                            'phone'  => $recipient['phone'],
                            'name'   => $recipient['name'],
                            'status' => 'failed',
                            'message'=> $smsResult['message'] ?? 'Failed to send SMS'
                        ];
                    }

                    // Log to DB
                    SmsLog::create([
                        'schoolID'       => $schoolID,
                        'message_id'     => $messageId,
                        'message_type'   => $messageType,
                        'recipient_name' => $recipient['name'],
                        'recipient_phone'=> $recipient['phone'],
                        'recipient_role' => $recipient['type'] ?? 'unknown',
                        'sent_by_name'   => $sentByName,
                        'sent_by_role'   => $sentByRole,
                        'message'        => $fullMessage,
                        'status'         => $status,
                        'sent_date'      => $sentDate,
                        'sent_time'      => $sentTime,
                    ]);

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['details'][] = [
                        'phone'  => $recipient['phone'],
                        'name'   => $recipient['name'],
                        'status' => 'failed',
                        'message'=> 'Exception: ' . $e->getMessage()
                    ];
                }

                usleep(100000); // 0.1 second delay
            }

            if ($batchIndex < count($batches) - 1) {
                sleep(1);
            }
        }
    }

    /**
     * Get SMS account balance
     */
    public function get_sms_balance()
    {
        try {
            $balanceResult = $this->smsService->getBalance();
            
            return response()->json([
                'success'  => $balanceResult['success'],
                'balance'  => $balanceResult['balance'] ?? 0,
                'currency' => $balanceResult['currency'] ?? 'TZS',
                'message'  => $balanceResult['message'] ?? 'Balance retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting SMS balance: ' . $e->getMessage());
            return response()->json([
                'success'  => false,
                'balance'  => 0,
                'currency' => 'TZS',
                'message'  => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SMS logs for delivery report
     * Filters by type and optional date range
     */
    public function get_sms_logs(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
            }

            $type      = $request->input('type');        // e.g. parent, teacher, staff
            $dateFrom  = $request->input('date_from');  // Y-m-d
            $dateTo    = $request->input('date_to');    // Y-m-d

            $query = SmsLog::where('schoolID', $schoolID);

            if ($type && $type !== 'all') {
                $query->where('message_type', $type);
            }

            if ($dateFrom) {
                $query->whereDate('sent_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('sent_date', '<=', $dateTo);
            }

            $logs = $query->orderByDesc('sent_date')
                          ->orderByDesc('sent_time')
                          ->get();

            return response()->json([
                'success' => true,
                'logs'    => $logs,
                'count'   => $logs->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting SMS logs: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch real-time delivery status for a single messageId from the SMS gateway
     */
    public function get_delivery_status(Request $request)
    {
        try {
            $messageId = $request->input('message_id');
            if (!$messageId) {
                return response()->json(['success' => false, 'message' => 'Message ID is required'], 400);
            }

            $username = config('services.sms.username', 'emcatechn');
            $password = config('services.sms.password', 'Emca@#12');
            $token    = base64_encode($username . ':' . $password);

            // Per documentation and gateway logs, use /api/sms/v1/reports
            $url = 'https://messaging-service.co.tz/api/sms/v1/reports?messageId=' . urlencode($messageId);
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Basic ' . $token,
                    'Accept: application/json'
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            
            // Use JSON_BIGINT_AS_STRING to prevent precision loss on long messageIds
            $data = json_decode($response, true, 512, JSON_BIGINT_AS_STRING) ?? [];

            Log::info("SMS Delivery Status Raw Response for MsgID {$messageId}: " . json_encode($data));

            // Extract status from possible response structures
            $status = 'UNKNOWN';
            
            // Check in results (standard structure)
            if (!empty($data['results'][0]['status']['groupName'])) {
                $status = $data['results'][0]['status']['groupName'];
            } 
            // Check in messages (alternate structure)
            elseif (!empty($data['messages'][0]['status']['groupName'])) {
                $status = $data['messages'][0]['status']['groupName'];
            }
            // Check if results is just a flat array
            elseif (isset($data[0]['status']['groupName'])) {
                $status = $data[0]['status']['groupName'];
            }

            // Map gateway status to our system status
            $mappedStatus = match(strtoupper($status)) {
                'DELIVERED', 'DELIVERED_TO_HANDSET', 'DELIVERED_TO_NETWORK' => 'DELIVERED',
                'PENDING', 'ACCEPTED', 'ENROUTE', 'UNDELIVERED'             => 'PENDING',
                'REJECTED', 'FAILED', 'UNDELIVERABLE', 'EXPIRED'            => 'FAILED',
                default                                                     => strtoupper($status)
            };

            // Update in DB
            SmsLog::where('message_id', $messageId)->update(['status' => $mappedStatus]);

            return response()->json([
                'success'    => true,
                'message_id' => $messageId,
                'status'     => $mappedStatus,
                'raw'        => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching delivery status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

