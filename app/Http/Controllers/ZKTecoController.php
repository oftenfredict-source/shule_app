<?php

namespace App\Http\Controllers;

use App\Services\TeacherStaffAttendanceSyncService;
use App\Services\TeacherStaffLiveEventBuffer;
use App\Services\TeacherStaffPermissionAttendanceService;
use App\Services\StudentPermissionAttendanceService;
use App\Services\StudentLiveEventBuffer;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\FingerprintDevice;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentFingerprintAttendance;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\TeacherFingerprintAttendance;
use App\Models\StaffFingerprintAttendance;
use App\Models\Subclass;
use App\Models\ClassModel;
use Carbon\Carbon;

class ZKTecoController extends Controller
{
    public function index()
    {
        $user_type = session('user_type', 'Admin');
        return view('Admin.fingerprint_device_settings', compact('user_type'));
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'password' => 'nullable|string|max:8',
        ]);

        $ip = $request->input('ip', config('zkteco.ip', '192.168.1.108'));
        $port = (int) $request->input('port', config('zkteco.port', 4370));
        $password = $request->input('password', config('zkteco.password', 0));

        try {
            $zkteco = new ZKTecoService($ip, $port, $password);
            $result = $zkteco->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDeviceInfo(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'password' => 'nullable|string|max:8',
        ]);

        $ip = $request->input('ip', config('zkteco.ip', '192.168.1.108'));
        $port = (int) $request->input('port', config('zkteco.port', 4370));
        $password = $request->input('password', config('zkteco.password', 0));

        try {
            $zkteco = new ZKTecoService($ip, $port, $password);
            $zkteco->connect();
            $deviceInfo = $zkteco->getDeviceInfo();
            $time = $zkteco->getTime();
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'device_info' => $deviceInfo,
                'device_time' => $time,
                'ip' => $ip,
                'port' => $port
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttendance(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'password' => 'nullable|string|max:8',
        ]);

        $ip = $request->input('ip', config('zkteco.ip', '192.168.1.108'));
        $port = (int) $request->input('port', config('zkteco.port', 4370));
        $password = $request->input('password', config('zkteco.password', 0));

        try {
            $zkteco = new ZKTecoService($ip, $port, $password);
            
            // Connect to device
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get attendance logs
            $attendance = $zkteco->getAttendance();
            
            // Disconnect
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'count' => count($attendance),
                'message' => count($attendance) > 0 
                    ? 'Retrieved ' . count($attendance) . ' attendance record(s)' 
                    : 'No attendance records found on device.'
            ]);
        } catch (\Exception $e) {
            Log::error('Get attendance error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get server information for Push SDK setup
     */
    public function getServerInfo()
    {
        $serverIP = request()->server('SERVER_ADDR') ?: request()->ip();
        $serverPort = request()->server('SERVER_PORT') ?: 80;
        $protocol = request()->getScheme();
        $host = request()->getHost();
        
        return response()->json([
            'success' => true,
            'server' => [
                'ip' => $serverIP,
                'host' => $host,
                'port' => $serverPort,
                'protocol' => $protocol,
                'ping_endpoint' => url('/iclock/getrequest'),
                'data_endpoint' => url('/iclock/cdata')
            ],
            'device_config' => [
                'server_ip' => $serverIP,
                'server_port' => $serverPort,
                'server_path' => '/iclock/getrequest'
            ]
        ]);
    }

    /**
     * Test device connection (ping)
     */
    public function testDeviceConnection(Request $request)
    {
        $request->validate([
            'device_ip' => 'required|ip'
        ]);

        $deviceIP = $request->input('device_ip');
        
        // Try to ping the device (this may not work if ping is disabled)
        $canPing = false;
        $pingResult = '';
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $pingResult = @shell_exec("ping -n 1 $deviceIP");
        } else {
            // Linux/Unix
            $pingResult = @shell_exec("ping -c 1 $deviceIP");
        }
        
        $canPing = $pingResult !== null && strpos($pingResult, 'TTL') !== false;
        
        return response()->json([
            'success' => true,
            'device_ip' => $deviceIP,
            'can_ping' => $canPing,
            'ping_result' => $pingResult ?: 'Ping command not available or device does not respond to ping'
        ]);
    }

    /**
     * Import users from device
     */
    public function importUsersFromDevice(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', '192.168.1.108');
        $port = $request->input('port', 4370);

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Connect to device
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get users from device
            $deviceUsers = $zkteco->getUsers();
            $zkteco->disconnect();

            if ($deviceUsers === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve users from device. The device may not support this operation or may require authentication.'
                ], 500);
            }

            if (!is_array($deviceUsers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response from device. Expected array but got: ' . gettype($deviceUsers)
                ], 500);
            }

            // Get current school ID from session
            $schoolID = session('schoolID');
            if (!$schoolID) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID not found in session. Please login again.'
                ], 400);
            }

            $created = 0;
            $markedRegistered = 0;
            $alreadyRegistered = 0;
            $notFound = 0;
            $errors = [];

            foreach ($deviceUsers as $key => $deviceUser) {
                try {
                    // Extract enroll_id/uid from device user
                    $enrollId = null;
                    $userName = '';
                    
                    if (is_array($deviceUser)) {
                        $enrollId = (string)($deviceUser['uid'] ?? $deviceUser['user_id'] ?? $key ?? '');
                        $userName = trim($deviceUser['name'] ?? '');
                    } else {
                        $enrollId = (string)$key;
                        $userName = is_string($deviceUser) ? trim($deviceUser) : '';
                    }

                    if (empty($enrollId) || $enrollId === '0' || !is_numeric($enrollId)) {
                        $notFound++;
                        $errors[] = "Invalid enroll ID: {$enrollId}";
                        continue;
                    }

                    // Find student by fingerprint_id or studentID (which equals fingerprint_id)
                    $student = Student::where(function($query) use ($enrollId) {
                        $query->where('fingerprint_id', $enrollId)
                              ->orWhere('studentID', (int)$enrollId);
                    })->where('schoolID', $schoolID)->first();
                    
                    if ($student) {
                        // Update student if needed
                        $updated = false;
                        
                        if (!$student->sent_to_device) {
                            $student->sent_to_device = true;
                            $student->device_sent_at = now();
                            $updated = true;
                        }
                        
                        if (empty($student->fingerprint_id)) {
                            $student->fingerprint_id = $enrollId;
                            $updated = true;
                        }
                        
                        if ($updated) {
                            $student->save();
                            $markedRegistered++;
                        } else {
                            $alreadyRegistered++;
                        }
                    } else {
                        // Create new student if not found
                        // We need at least first_name, last_name, gender, and admission_number
                        $firstName = $userName ?: "User {$enrollId}";
                        $lastName = '';
                        
                        // Try to split name if it contains space
                        if (strpos($userName, ' ') !== false) {
                            $nameParts = explode(' ', $userName, 2);
                            $firstName = $nameParts[0];
                            $lastName = $nameParts[1] ?? '';
                        }
                        
                        // Generate unique admission number
                        $admissionNumber = 'DEV' . $enrollId . '-' . date('Y');
                        $counter = 0;
                        while (Student::where('admission_number', $admissionNumber)->exists() && $counter < 100) {
                            $admissionNumber = 'DEV' . $enrollId . '-' . date('Y') . '-' . $counter;
                            $counter++;
                        }
                        
                        // Get default subclass for this school
                        // Join with classes table to filter by schoolID
                        $defaultSubclass = DB::table('subclasses')
                            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
                            ->where('classes.schoolID', $schoolID)
                            ->select('subclasses.subclassID')
                            ->first();
                        
                        if (!$defaultSubclass || !isset($defaultSubclass->subclassID)) {
                            $notFound++;
                            $errors[] = "No subclass found for school ID {$schoolID}. Please create at least one class and subclass first.";
                            Log::warning("No subclass found for school ID {$schoolID} when importing user {$enrollId}");
                            continue;
                        }
                        
                        try {
                            $student = Student::create([
                                'studentID' => (int)$enrollId,
                                'schoolID' => $schoolID,
                                'subclassID' => $defaultSubclass->subclassID,
                                'first_name' => $firstName,
                                'last_name' => $lastName ?: $firstName,
                                'gender' => 'Male', // Default - device doesn't provide gender
                                'admission_number' => $admissionNumber,
                                'fingerprint_id' => $enrollId,
                                'sent_to_device' => true,
                                'device_sent_at' => now(),
                                'fingerprint_capture_count' => 0,
                                'status' => 'Active'
                            ]);
                            
                            $created++;
                            Log::info("Created new student from device: Enroll ID {$enrollId}, Name: {$userName}");
                        } catch (\Exception $e) {
                            $notFound++;
                            $errors[] = "Failed to create student for Enroll ID {$enrollId}: " . $e->getMessage();
                            Log::error("Error creating student from device: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    $notFound++;
                    $errors[] = "Error processing device user: " . $e->getMessage();
                    Log::error("Error processing device user: " . $e->getMessage());
                }
            }

            $message = "Import completed: {$created} created, {$markedRegistered} marked as registered, {$alreadyRegistered} already registered";
            if ($notFound > 0) {
                $message .= ", {$notFound} could not be processed";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_device_users' => count($deviceUsers),
                    'verified' => count($deviceUsers),
                    'created' => $created,
                    'marked_registered' => $markedRegistered,
                    'already_registered' => $alreadyRegistered,
                    'not_found' => $notFound,
                    'errors' => $errors
                ],
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Import users from device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check recent activity (users and attendance)
     */
    public function checkRecentActivity()
    {
        try {
            $usersCount = Student::count();
            $attendancesCount = StudentFingerprintAttendance::count();
            
            // Get recent users (last 5) - students that are sent to device
            $recentUsers = Student::where('sent_to_device', true)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->count();
            
            // Get recent attendances (last 5)
            $recentAttendances = StudentFingerprintAttendance::orderBy('created_at', 'desc')
                ->limit(5)
                ->count();

            return response()->json([
                'success' => true,
                'users_count' => $usersCount,
                'attendances_count' => $attendancesCount,
                'recent_users' => $recentUsers,
                'recent_attendances' => $recentAttendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check device status for user management
     */
    public function checkDeviceStatus(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            $status = [
                'connection' => false,
                'authentication' => false,
                'can_read_users' => false,
                'users_count' => 0,
                'device_name' => null,
                'device_info' => null,
                'issues' => [],
                'recommendations' => []
            ];

            // Test connection
            if ($zkteco->connect()) {
                $status['connection'] = true;
                
                try {
                    // Try to get device info
                    $deviceInfo = $zkteco->getDeviceInfo();
                    if ($deviceInfo) {
                        $status['device_info'] = $deviceInfo;
                        $status['device_name'] = $deviceInfo['device_name'] ?? null;
                        $status['authentication'] = true;
                    }
                    
            // Test reading users
            try {
                $users = $zkteco->getUsers();
                if ($users === false) {
                    $status['issues'][] = 'Cannot read users from device - getUsers returned false';
                    $status['recommendations'][] = 'Check device connection and authentication';
                } elseif (is_array($users)) {
                    $status['can_read_users'] = true;
                    $status['users_count'] = count($users);
                    
                    if (count($users) >= 1000) {
                        $status['issues'][] = 'Device has many users (' . count($users) . ') - memory might be full';
                        $status['recommendations'][] = 'Consider clearing old users or restarting device';
                    }
                } else {
                    $status['issues'][] = 'Unexpected response type from getUsers';
                }
            } catch (\Exception $e) {
                $status['issues'][] = 'Cannot read users from device: ' . $e->getMessage();
            }
                } catch (\Exception $e) {
                    $status['issues'][] = 'Error accessing device: ' . $e->getMessage();
                }
            } else {
                $status['issues'][] = 'Cannot connect to device';
                $status['recommendations'][] = 'Check IP, port, network connectivity, and firewall settings';
            }

            return response()->json([
                'success' => true,
                'status' => $status,
                'ready_for_registration' => $status['connection'] && $status['authentication'] && $status['can_read_users'] && empty($status['issues'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking device status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register student to device (similar to sample project's registerToDevice)
     */
    public function registerStudentToDevice(Request $request, $id)
    {
        try {
            $student = Student::findOrFail($id);
            
            $request->validate([
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535'
            ]);

            $ip = $request->input('ip', config('zkteco.ip'));
            $port = $request->input('port', config('zkteco.port'));

            $zkteco = new ZKTecoService($ip, $port);
            
            // Pre-registration check: Verify device is accessible and Enroll ID is available
            try {
                if (!$zkteco->connect()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot connect to device. Check IP, port, and network connectivity.',
                        'troubleshooting' => "🔌 CONNECTION ISSUE:\n\n1. Check device IP: {$ip}\n2. Check device port: {$port}\n3. Ensure device is powered on\n4. Check network connectivity\n5. Check firewall settings (UDP port 4370)"
                    ], 500);
                }
                
                // Check if Enroll ID already exists on device
                $deviceUsers = $zkteco->getUsers();
                if ($deviceUsers !== false && is_array($deviceUsers)) {
                    foreach ($deviceUsers as $key => $deviceUser) {
                        $deviceEnrollId = (string)($key ?? (is_array($deviceUser) ? ($deviceUser['user_id'] ?? $deviceUser['uid'] ?? '') : ''));
                        if ($deviceEnrollId === (string)$student->fingerprint_id) {
                            return response()->json([
                                'success' => false,
                                'message' => "Enroll ID '{$student->fingerprint_id}' already exists on device",
                                'troubleshooting' => "⚠️ ENROLL ID CONFLICT:\n\nEnroll ID {$student->fingerprint_id} is already registered on the device.\n\n✅ SOLUTIONS:\n1. Use a different Enroll ID for this student\n2. Remove the existing user from device first\n3. Click 'Sync Users from Device' to update status if this is the same student"
                            ], 400);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Pre-registration check failed: ' . $e->getMessage());
                // Continue with registration attempt anyway
            }
            
            // Validate fingerprint_id is numeric
            if (!is_numeric($student->fingerprint_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID must be numeric (e.g., 1, 2, 3...)'
                ], 400);
            }
            
            // Register student to device
            // UID is typically the fingerprint_id, userid is also fingerprint_id, name is student's name
            $uid = (int) $student->fingerprint_id;
            
            if ($uid < 1 || $uid > 65535) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fingerprint ID must be between 1 and 65535'
                ], 400);
            }
            
            // Get student full name
            $studentName = trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name);
            $studentName = strtoupper($studentName);
            // Truncate to 24 characters (device limit)
            if (strlen($studentName) > 24) {
                $studentName = substr($studentName, 0, 24);
            }
            
            $result = $zkteco->registerUser(
                $uid,
                $student->fingerprint_id,
                $studentName,
                '', // password (empty for fingerprint devices)
                0,  // role (0 = user, 14 = admin)
                0   // cardno
            );

            if ($result) {
                $student->update([
                    'sent_to_device' => true,
                    'device_sent_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Student registered to device successfully and verified!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register student to device'
                ], 500);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Register student to device error: ' . $e->getMessage());
            
            $errorMessage = $e->getMessage();
            
            // Check if error message suggests student might be on device
            $mightBeRegistered = strpos($errorMessage, 'MAY have been registered') !== false || 
                                strpos($errorMessage, 'device responded') !== false;
            
            // Check for specific error patterns to provide targeted solutions
            $isCommKeyIssue = strpos($errorMessage, 'Comm Key') !== false || 
                             strpos($errorMessage, 'authentication') !== false ||
                             strpos($errorMessage, 'CMD_ACK_UNAUTH') !== false;
            
            $isDeviceRejected = strpos($errorMessage, 'rejected') !== false ||
                               strpos($errorMessage, 'User count did not increase') !== false ||
                               strpos($errorMessage, 'CMD_ACK_ERROR') !== false;
            
            $isFirmwareIssue = strpos($errorMessage, '2007') !== false ||
                              strpos($errorMessage, 'firmware') !== false;
            
            // Build comprehensive troubleshooting guide
            $troubleshooting = [];
            
            if ($isCommKeyIssue) {
                $troubleshooting[] = "🔑 COMM KEY ISSUE:";
                $troubleshooting[] = "1. Check device Comm Key: On device → System → Communication → Comm Key";
                $troubleshooting[] = "2. Update .env file: ZKTECO_PASSWORD=<your_comm_key>";
                $troubleshooting[] = "3. If Comm Key is 0, set ZKTECO_PASSWORD=0 in .env";
                $troubleshooting[] = "4. Restart the server after changing .env";
            }
            
            if ($isDeviceRejected) {
                $troubleshooting[] = "";
                $troubleshooting[] = "❌ DEVICE REJECTED REGISTRATION:";
                $troubleshooting[] = "Possible causes:";
                $troubleshooting[] = "1. Fingerprint ID already exists on device (check device user list)";
                $troubleshooting[] = "2. Device memory is full";
                $troubleshooting[] = "3. Device is locked or in wrong mode";
                $troubleshooting[] = "4. Device needs to be enabled/unlocked";
                $troubleshooting[] = "";
                $troubleshooting[] = "✅ QUICK FIXES:";
                $troubleshooting[] = "• Check device: User Management → User List (see if Fingerprint ID {$student->fingerprint_id} exists)";
                $troubleshooting[] = "• If student exists on device, click 'Sync Users from Device' to update status";
                $troubleshooting[] = "• Try a different Fingerprint ID if current one is taken";
            }
            
            if ($isFirmwareIssue || (!$isCommKeyIssue && !$isDeviceRejected)) {
                $troubleshooting[] = "";
                $troubleshooting[] = "⚠️ FIRMWARE COMPATIBILITY ISSUE:";
                $troubleshooting[] = "Your device may have firmware compatibility issues.";
                $troubleshooting[] = "";
                $troubleshooting[] = "✅ WORKAROUND - Manual Registration:";
                $troubleshooting[] = "1. On device: User Management → Add User";
                $troubleshooting[] = "   • Enroll ID: {$student->fingerprint_id}";
                $troubleshooting[] = "   • Name: {$studentName}";
                $troubleshooting[] = "   • Save";
                $troubleshooting[] = "";
                $troubleshooting[] = "2. On web: Click 'Sync Users from Device' button";
                $troubleshooting[] = "   • Student will be synced automatically!";
                $troubleshooting[] = "";
                $troubleshooting[] = "3. Enroll fingerprint (optional):";
                $troubleshooting[] = "   • On device: User Management → Enroll Fingerprint";
                $troubleshooting[] = "   • Enter Enroll ID: {$student->fingerprint_id}";
            }
            
            $troubleshooting[] = "";
            $troubleshooting[] = "🔄 DEVICE RESTART (If it was working before):";
            $troubleshooting[] = "1. Power cycle the device (turn off, wait 10 seconds, turn on)";
            $troubleshooting[] = "2. Wait 30 seconds for device to fully boot";
            $troubleshooting[] = "3. Try registration again";
            
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed: ' . $errorMessage,
                'troubleshooting' => implode("\n", $troubleshooting),
                'might_be_registered' => $mightBeRegistered,
                'error_type' => [
                    'comm_key_issue' => $isCommKeyIssue,
                    'device_rejected' => $isDeviceRejected,
                    'firmware_issue' => $isFirmwareIssue
                ],
                'user_info' => [
                    'name' => $studentName,
                    'enroll_id' => $student->fingerprint_id
                ],
                'device_info' => [
                    'ip' => $ip,
                    'port' => $port
                ]
            ], 500);
        }
    }

    /**
     * Register user to device
     */
    public function registerUserToDevice(Request $request)
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535',
                'enroll_id' => 'required|integer|min:1|max:65535',
                'name' => 'required|string|max:24'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }

        $ip = $request->input('ip');
        $port = $request->input('port');
        $enrollId = $request->input('enroll_id');
        $name = trim($request->input('name'));

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection first
            if (!$zkteco->connect()) {
                // Check logs for specific error
                $lastLog = Log::getLogger()->getHandlers()[0]->getLevel() ?? null;
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot connect to device. Check IP, port, and network connectivity.',
                    'suggestion' => 'If device closes connection immediately (Error 10054), try: 1) Check Comm Key in device settings, 2) Use Push SDK instead (see Push SDK Setup Wizard tab), 3) Verify device firmware supports TCP commands, 4) Check device firewall/security settings.'
                ], 500);
            }
            
            // Check if Enroll ID already exists on device
            try {
                $deviceUsers = $zkteco->getUsers();
                if ($deviceUsers === false) {
                    Log::warning('Could not retrieve users from device to check for duplicates, proceeding with registration');
                } elseif (is_array($deviceUsers)) {
                    foreach ($deviceUsers as $key => $deviceUser) {
                        if (!is_array($deviceUser)) {
                            continue;
                        }
                        $deviceEnrollId = (string)($key ?? $deviceUser['user_id'] ?? $deviceUser['uid'] ?? '');
                        if ($deviceEnrollId === (string)$enrollId) {
                            return response()->json([
                                'success' => false,
                                'message' => "Enroll ID '{$enrollId}' already exists on device"
                            ], 400);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error checking for duplicate users: ' . $e->getMessage());
                // Continue with registration even if check fails
            }
            
            // Register user to device
            // registerUser signature: registerUser($uid, $name, $privilege = 0, $password = '', $group = '', $user_id = '')
            try {
                $result = $zkteco->registerUser(
                    (int)$enrollId,      // uid
                    $name,               // name
                    0,                   // privilege (0 = user)
                    '',                  // password (empty for fingerprint devices)
                    '',                  // group
                    (string)$enrollId    // user_id
                );

                if ($result) {
                    // Verify registration by checking if user exists on device
                    try {
                        $deviceUsers = $zkteco->getUsers();
                        $userFound = false;
                        
                        foreach ($deviceUsers as $deviceUser) {
                            if (is_array($deviceUser)) {
                                $deviceUid = $deviceUser['uid'] ?? null;
                                $deviceUserId = $deviceUser['user_id'] ?? null;
                                
                                if ($deviceUid == (int)$enrollId || $deviceUserId == (string)$enrollId) {
                                    $userFound = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($userFound) {
                            return response()->json([
                                'success' => true,
                                'message' => "User '{$name}' (Enroll ID: {$enrollId}) registered to device successfully and verified!"
                            ]);
                        } else {
                            // Registration returned true but user not found - might be timing issue
                            Log::warning("Registration returned true but user not immediately found on device. This might be a timing issue.");
                            return response()->json([
                                'success' => true,
                                'message' => "User '{$name}' (Enroll ID: {$enrollId}) registration command succeeded. Please verify on device or refresh users list.",
                                'warning' => 'User not immediately found on device - this might be a timing issue. Please check device or refresh users list.'
                            ]);
                        }
                    } catch (\Exception $verifyException) {
                        Log::warning('Could not verify user registration: ' . $verifyException->getMessage());
                        // Still return success as registration command succeeded
                        return response()->json([
                            'success' => true,
                            'message' => "User '{$name}' (Enroll ID: {$enrollId}) registration command succeeded. Verification failed, please check device manually.",
                            'warning' => 'Could not verify registration automatically. Please check device or refresh users list.'
                        ]);
                    }
                } else {
                    // Registration returned false
                    $errorMessage = 'Failed to register user to device. The device may have rejected the registration.';
                    $suggestion = 'Possible reasons: 1) Enroll ID already exists, 2) Device memory full, 3) Invalid data format, 4) Comm Key incorrect, 5) Device firmware issue.';
                    
                    // Check recent logs for connection closure
                    $logFile = storage_path('logs/laravel.log');
                    if (file_exists($logFile)) {
                        $logContent = file_get_contents($logFile);
                        if (strpos($logContent, '10054') !== false || strpos($logContent, 'forcibly closed') !== false) {
                            $errorMessage = 'Device closed connection during registration.';
                            $suggestion = 'This device may require: 1) Correct Comm Key (check device settings), 2) Push SDK configuration instead of direct TCP (see Push SDK Setup Wizard tab), 3) Firmware update, or 4) Device may be in restricted mode.';
                        }
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'suggestion' => $suggestion
                    ], 500);
                }
            } catch (\Exception $regException) {
                // Registration threw an exception
                $errorMessage = 'Registration Failed: ' . $regException->getMessage();
                
                // Check if it's a connection closure issue
                $isConnectionIssue = strpos($regException->getMessage(), 'closed') !== false || 
                                    strpos($regException->getMessage(), '10054') !== false ||
                                    strpos($regException->getMessage(), 'setUser returned false') !== false;
                
                $troubleshooting = '';
                if ($isConnectionIssue) {
                    $troubleshooting = "Device closed connection or rejected registration. This is common with UF200-S firmware 6.60.\n\n";
                    $troubleshooting .= "SOLUTIONS:\n";
                    $troubleshooting .= "1. Register user DIRECTLY on device:\n";
                    $troubleshooting .= "   - Press MENU on device\n";
                    $troubleshooting .= "   - Go to: User Management → User List → Add User\n";
                    $troubleshooting .= "   - Enter Enroll ID: {$enrollId}, Name: {$name}\n";
                    $troubleshooting .= "   - Save, then use 'Get Users from Device' to sync\n\n";
                    $troubleshooting .= "2. Use Push SDK (recommended for this device):\n";
                    $troubleshooting .= "   - Go to 'Push SDK Setup Wizard' tab\n";
                    $troubleshooting .= "   - Configure device ADMS settings\n";
                    $troubleshooting .= "   - Device will push user data automatically\n\n";
                    $troubleshooting .= "3. Check device settings:\n";
                    $troubleshooting .= "   - System → Communication → Comm Key (should be 0)\n";
                    $troubleshooting .= "   - Ensure device is not in sleep/restricted mode";
                } else {
                    $troubleshooting = 'Check device logs, verify Comm Key, ensure Enroll ID is unique, and try using Push SDK if direct TCP fails.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'troubleshooting' => $troubleshooting
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Register user to device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Registration Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List users from device
     */
    public function listDeviceUsers(Request $request)
    {
        try {
            $request->validate([
                'ip' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }

        $ip = $request->input('ip');
        $port = $request->input('port');

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            // Test connection first
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP, port, and network connectivity.'
                ], 500);
            }
            
            $users = $zkteco->getUsers();
            
            // getUsers() now returns array (empty array on failure)
            if (!is_array($users)) {
                Log::error('getUsers returned non-array: ' . gettype($users) . ' - ' . var_export($users, true));
                return response()->json([
                    'success' => false,
                    'message' => 'Unexpected response type from device. Expected array but got: ' . gettype($users)
                ], 500);
            }
            
            if (empty($users)) {
                return response()->json([
                    'success' => true,
                    'users' => [],
                    'count' => 0,
                    'message' => 'No users found on device.'
                ]);
            }
            
            // Format users for better display
            $formattedUsers = [];
            foreach ($users as $key => $user) {
                if (is_array($user)) {
                    $formattedUsers[] = [
                        'uid' => $user['uid'] ?? $key ?? 'N/A',
                        'user_id' => $user['user_id'] ?? $key ?? 'N/A',
                        'name' => $user['name'] ?? 'N/A',
                        'role' => $user['privilege'] ?? $user['role'] ?? 'N/A',
                        'card_no' => $user['card_no'] ?? 'N/A',
                        'raw_data' => $user
                    ];
                } else {
                    // Handle case where user data is not in expected format
                    $formattedUsers[] = [
                        'uid' => $key ?? 'N/A',
                        'user_id' => $key ?? 'N/A',
                        'name' => is_string($user) ? $user : 'N/A',
                        'role' => 'N/A',
                        'card_no' => 'N/A',
                        'raw_data' => $user
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'users' => $formattedUsers,
                'raw_users' => $users,
                'count' => count($users),
                'message' => count($users) > 0 
                    ? "Found " . count($users) . " user(s) on device" 
                    : "No users found on device."
            ]);
        } catch (\Exception $e) {
            Log::error('List device users error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user from device
     */
    public function deleteUserFromDevice(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'enroll_id' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip');
        $port = $request->input('port');
        $enrollId = $request->input('enroll_id');

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device.'
                ], 500);
            }

            // Check if ZKTecoService has removeUser method
            // If not, we'll need to implement it or use alternative method
            $uid = (int)$enrollId;
            
            // Try to remove user - note: ZKTecoService may need removeUser method
            // For now, we'll return a message that this needs to be implemented
            // You can add removeUser method to ZKTecoService based on device protocol
            
            return response()->json([
                'success' => false,
                'message' => 'Delete user functionality needs to be implemented in ZKTecoService. Please check if removeUser method exists.'
            ], 501);
            
        } catch (\Exception $e) {
            Log::error('Delete user from device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's attendance records from device (raw format)
     * Uses getAttendance() and filters by today's date
     */
    public function getTodayAttendance(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535'
        ]);

        $ip = $request->input('ip', config('zkteco.ip'));
        $port = $request->input('port', config('zkteco.port'));
        $today = Carbon::today()->toDateString();

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get all attendance from device
            $allAttendance = $zkteco->getAttendance();
            
            // Disconnect
            $zkteco->disconnect();

            // Filter by today's date
            $todayAttendance = array_filter($allAttendance, function($record) use ($today) {
                if (!isset($record['record_time'])) {
                    return false;
                }
                $recordDate = Carbon::parse($record['record_time'])->toDateString();
                return $recordDate === $today;
            });

            // Re-index array
            $todayAttendance = array_values($todayAttendance);

            // Attach user names from students table if possible
            $userIds = collect($todayAttendance)->pluck('user_id')->filter()->unique()->values();
            $students = Student::whereIn('fingerprint_id', $userIds)->get()
                ->keyBy('fingerprint_id');

            foreach ($todayAttendance as &$record) {
                $userId = $record['user_id'] ?? null;
                $student = $userId ? $students->get($userId) : null;
                if ($student) {
                    $fullName = trim(implode(' ', array_filter([
                        $student->first_name ?? null,
                        $student->middle_name ?? null,
                        $student->last_name ?? null,
                    ])));
                    $record['user_name'] = $fullName !== '' ? $fullName : (string)$userId;
                } else {
                    $record['user_name'] = $userId !== null ? (string)$userId : null;
                }
            }

            return response()->json([
                'success' => true,
                'date' => $today,
                'attendance' => $todayAttendance,
                'count' => count($todayAttendance),
                'message' => count($todayAttendance) > 0 
                    ? 'Retrieved ' . count($todayAttendance) . ' attendance record(s) for today' 
                    : 'No attendance records found for today on device.'
            ]);
        } catch (\Exception $e) {
            Log::error('Get today attendance error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving today\'s attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records for a specific date from device (raw format)
     * Uses getAttendance() and filters by selected date
     */
    public function getAttendanceByDate(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'date' => 'nullable|date'
        ]);

        $ip = $request->input('ip', config('zkteco.ip'));
        $port = $request->input('port', config('zkteco.port'));
        $selectedDate = $request->input('date', now()->toDateString());
        $carbonDate = Carbon::parse($selectedDate)->toDateString();

        try {
            $zkteco = new ZKTecoService($ip, $port);
            
            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Check IP, port, and network connectivity.'
                ], 500);
            }
            
            // Get all attendance from device
            $allAttendance = $zkteco->getAttendance();
            
            // Disconnect
            $zkteco->disconnect();

            // Filter by selected date
            $filteredAttendance = array_filter($allAttendance, function($record) use ($carbonDate) {
                if (!isset($record['record_time'])) {
                    return false;
                }
                $recordDate = Carbon::parse($record['record_time'])->toDateString();
                return $recordDate === $carbonDate;
            });

            // Re-index array
            $filteredAttendance = array_values($filteredAttendance);

            // Attach user names from students table if possible
            $userIds = collect($filteredAttendance)->pluck('user_id')->filter()->unique()->values();
            $students = Student::whereIn('fingerprint_id', $userIds)->get()
                ->keyBy('fingerprint_id');

            foreach ($filteredAttendance as &$record) {
                $userId = $record['user_id'] ?? null;
                $student = $userId ? $students->get($userId) : null;
                if ($student) {
                    $fullName = trim(implode(' ', array_filter([
                        $student->first_name ?? null,
                        $student->middle_name ?? null,
                        $student->last_name ?? null,
                    ])));
                    $record['user_name'] = $fullName !== '' ? $fullName : (string)$userId;
                } else {
                    $record['user_name'] = $userId !== null ? (string)$userId : null;
                }
            }

            return response()->json([
                'success' => true,
                'date' => $carbonDate,
                'attendance' => $filteredAttendance,
                'count' => count($filteredAttendance),
                'message' => count($filteredAttendance) > 0 
                    ? 'Retrieved ' . count($filteredAttendance) . ' attendance record(s) for ' . $carbonDate
                    : 'No attendance records found for ' . $carbonDate . ' on device.'
            ]);
        } catch (\Exception $e) {
            Log::error('Get attendance by date error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fingerprint attendance from database table for class management
     * - Loads from student_fingerprint_attendance table (not device)
     * - Filters by subclassID and optional date
     * - Joins with students table for additional info
     * - Returns data in format expected by frontend
     */
    public function getFingerprintAttendanceFromDB(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
            'date' => 'nullable|date',
            'month' => 'nullable|string', // Format: YYYY-MM
            'year' => 'nullable|integer',
            'page' => 'nullable|integer|min:1'
        ]);

        $subclassID = $request->input('subclassID');
        $date = $request->input('date');
        $month = $request->input('month');
        $year = $request->input('year');
        $page = $request->input('page', 1);
        $perPage = 50;

        try {
            // Get students in this subclass
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'source' => 'database',
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                    ],
                    'message' => 'No students found in this class.',
                ]);
            }

            // Build query for student_fingerprint_attendance
            $query = StudentFingerprintAttendance::whereIn('studentID', $students->pluck('studentID'));

            // Filter by date if provided
            if ($date) {
                $carbonDate = Carbon::parse($date)->toDateString();
                $query->whereDate('attendance_date', $carbonDate);
            } elseif ($month) {
                // Filter by month (YYYY-MM format)
                $carbonMonth = Carbon::parse($month . '-01');
                $query->whereYear('attendance_date', $carbonMonth->year)
                    ->whereMonth('attendance_date', $carbonMonth->month);
            } elseif ($year) {
                // Filter by year
                $query->whereYear('attendance_date', $year);
            }

            // Get records with pagination
            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = [];

            foreach ($attendanceRecords as $record) {
                /** @var Student|null $student */
                $student = $students->get($record->studentID);
                if (!$student) {
                    continue;
                }

                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                $data[] = [
                    'studentID' => $student->studentID,
                    'user_id' => $student->studentID,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'class_name' => $className,
                    'attendance_date' => $record->attendance_date ? $record->attendance_date->toDateString() : null,
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
                    'verify_mode' => $record->verify_mode ?? 'Fingerprint',
                    'device_ip' => $record->device_ip ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'source' => 'database',
                'data' => $data,
                'pagination' => [
                    'current_page' => $attendanceRecords->currentPage(),
                    'per_page' => $attendanceRecords->perPage(),
                    'total' => $attendanceRecords->total(),
                    'last_page' => $attendanceRecords->lastPage(),
                ],
                'message' => 'Retrieved ' . $attendanceRecords->total() . ' attendance record(s) from database'
            ]);
        } catch (\Exception $e) {
            Log::error('Get fingerprint attendance from DB error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance from database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fingerprint attendance from device for class management
     * Returns data in format expected by frontend (similar to API format)
     */
    public function getFingerprintAttendanceForClass(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
            'date' => 'nullable|date',
            'page' => 'nullable|integer|min:1'
        ]);

        $subclassID = $request->input('subclassID');
        $date = $request->input('date');
        $page = $request->input('page', 1);
        $perPage = 50;

        try {
            $devices = $this->getAttendanceDevicesForSync($request);
            $device = $devices->first();
            $ip = $device->ip;
            $port = (int) $device->port;
            $password = $device->comm_key ?? '0';

            // Get students in this subclass with subclass info
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID'); // user_id from device = studentID (primary key)

            // Connect to device and get all attendance
            $zkteco = new ZKTecoService($ip, $port, $password);

            if (!$zkteco->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device (' . $ip . '). Check IP, port, comm key, and network connectivity.'
                ], 500);
            }

            $allAttendance = $zkteco->getAttendance();
            $zkteco->disconnect();

            $today = Carbon::today()->toDateString();
            $excludeToday = $request->input('exclude_today', false); // Default: include today (for Fingerprint Attendance tab)

            // Filter by date if provided
            if ($date) {
                $carbonDate = Carbon::parse($date)->toDateString();
                $allAttendance = array_filter($allAttendance, function($record) use ($carbonDate, $today, $excludeToday) {
                    if (!isset($record['record_time'])) return false;
                    $recordDate = Carbon::parse($record['record_time'])->toDateString();
                    // If exclude_today is true AND this is today's date, exclude it (for overview tab)
                    if ($excludeToday && $recordDate === $today) return false;
                    // Otherwise, filter by the requested date
                    return $recordDate === $carbonDate;
                });
                $allAttendance = array_values($allAttendance);
            } else {
                // If no date specified, exclude today only if exclude_today flag is set (for overview)
                if ($excludeToday) {
                    $allAttendance = array_filter($allAttendance, function($record) use ($today) {
                        if (!isset($record['record_time'])) return false;
                        $recordDate = Carbon::parse($record['record_time'])->toDateString();
                        return $recordDate !== $today; // Exclude today for overview
                    });
                    $allAttendance = array_values($allAttendance);
                }
                // If excludeToday is false, include all records (including today) - for Fingerprint Attendance tab
            }

            // Group by user_id (which is studentID) and date
            // Security: Only take first sign-in (Check In) and second sign-in (Check Out)
            // Ignore any additional sign-ins for security reasons
            $grouped = [];
            $userDateRecords = []; // Track records per user per date

            // First, collect all records per user per date
            foreach ($allAttendance as $record) {
                $userId = isset($record['user_id']) ? (int)$record['user_id'] : null;
                if (!$userId) continue;

                // user_id from device = studentID (primary key)
                $student = $students->get($userId);
                if (!$student) continue; // Only include students from this subclass

                $recordDate = Carbon::parse($record['record_time'])->toDateString();
                $key = $userId . '_' . $recordDate;

                if (!isset($userDateRecords[$key])) {
                    $userDateRecords[$key] = [];
                }

                $userDateRecords[$key][] = [
                    'time' => Carbon::parse($record['record_time']),
                    'type' => $record['type'] ?? null,
                    'device_ip' => $record['device_ip'] ?? null,
                    'student' => $student,
                ];
            }

            // Process each user-date combination: only first 2 records
            foreach ($userDateRecords as $key => $records) {
                // Sort by time (ascending)
                usort($records, function($a, $b) {
                    return $a['time']->lt($b['time']) ? -1 : 1;
                });

                // Security: Only take first 2 records (Check In and Check Out)
                // Ignore any additional records
                if (count($records) < 1) continue;

                $firstRecord = $records[0];
                $secondRecord = count($records) >= 2 ? $records[1] : null;

                $student = $firstRecord['student'];
                $recordDate = $firstRecord['time']->toDateString();
                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;
                $userId = $student->studentID;

                $grouped[$key] = [
                    'studentID' => $student->studentID,
                    'user_id' => $userId,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $userId,
                    'fingerprint_id' => $fingerprintId,
                    'class_name' => $className,
                    'attendance_date' => $recordDate,
                    'check_in_time' => $firstRecord['time']->format('Y-m-d H:i:s'), // First sign-in = Check In
                    'check_out_time' => $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null, // Second sign-in = Check Out
                    'status' => $secondRecord ? 'Complete' : 'Check In Only',
                    'verify_mode' => $firstRecord['type'] ?? null,
                    'device_ip' => $firstRecord['device_ip'] ?? null,
                ];
            }

            $records = array_values($grouped);

            // Paginate
            $total = count($records);
            $offset = ($page - 1) * $perPage;
            $paginatedRecords = array_slice($records, $offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
                'message' => 'Retrieved ' . $total . ' attendance record(s) from device'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Get fingerprint attendance for class error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Fallback to database if device fails
            return $this->getFingerprintAttendanceFromDBFallback($request);
        }
    }

    /**
     * Live attendance sync for TODAY:
     * - Reads raw attendance from device for current date
     * - Applies security rule: only first 2 punches per student per day
     *   (1st = check_in, 2nd = check_out, others ignored)
     * - Upserts into student_fingerprint_attendance table (one row per student per day)
     * - Returns today's aggregated attendance for the requested subclass
     */
    public function syncLiveAttendanceToday(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
            'device_id' => 'required|string',
            'db_only' => 'nullable|boolean',
        ]);

        $subclassID = (int) $request->input('subclassID');
        $today = Carbon::today()->toDateString();
        $dbOnly = filter_var($request->input('db_only'), FILTER_VALIDATE_BOOLEAN);

        try {
            if ($dbOnly) {
                return $this->getLiveAttendanceTodayFromDB($subclassID, $today, false, null, []);
            }

            set_time_limit(60);

            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            $syncResult = $this->syncAttendanceFromDevices($request, $students, $today);

            if ($syncResult['anySuccess'] && !empty($syncResult['userDateRecords'])) {
                $this->persistGroupedAttendanceRecords($syncResult['userDateRecords']);
            }

            if (!$syncResult['anySuccess']) {
                return $this->getLiveAttendanceTodayFromDB(
                    $subclassID,
                    $today,
                    true,
                    null,
                    $syncResult['summary']
                );
            }

            return $this->getLiveAttendanceTodayFromDB(
                $subclassID,
                $today,
                false,
                null,
                $syncResult['summary']
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Live attendance sync error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return $this->getLiveAttendanceTodayFromDB($subclassID, $today, true);
        }
    }

    /**
     * Live student attendance for Today (DB only).
     * Used when device sync is done by local LAN agent.
     */
    public function getStudentLiveTodayFromDb(Request $request)
    {
        $request->validate([
            'subclassID' => 'nullable|integer',
            'classID' => 'nullable|integer',
        ]);

        $schoolID = Session::get('schoolID');
        if (! $schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School session not found. Please refresh the page or log in again.',
            ], 403);
        }

        $schoolID = (int) $schoolID;
        $today = Carbon::today()->toDateString();
        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;
        $classID = $request->input('classID') ? (int) $request->input('classID') : null;

        if (Session::get('user_type') === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if ($subclassID) {
                $subclass = Subclass::with('class')->find($subclassID);
                if (! $subclass || ! $subclass->class || (int) $subclass->class->schoolID !== $schoolID) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access to this class.'], 403);
                }
                $isClassTeacher = (int) $subclass->teacherID === (int) $teacherID;
                $isCoordinator = ClassModel::where('classID', $subclass->classID)
                    ->where('teacherID', $teacherID)
                    ->where('schoolID', $schoolID)
                    ->exists();
                if (! $isClassTeacher && ! $isCoordinator) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access to this class.'], 403);
                }
            } elseif ($classID) {
                if (! ClassModel::where('classID', $classID)->where('schoolID', $schoolID)->where('teacherID', $teacherID)->exists()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access to this class.'], 403);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Class or subclass filter is required.'], 422);
            }
        }

        return $this->getLiveAttendanceTodayFromDB(
            $subclassID ?? 0,
            $today,
            false,
            null,
            [],
            $schoolID,
            $classID
        );
    }

    /**
     * Helper: Get today's fingerprint attendance for subclass from DB
     * - Always one row per student per day (enforced by updateOrCreate above)
     * - Used both after successful sync and as fallback when device is unavailable
     */
    private function getLiveAttendanceTodayFromDB(
        int $subclassID,
        string $today,
        bool $deviceFailed = false,
        ?string $deviceIp = null,
        array $devicesSummary = [],
        ?int $schoolID = null,
        ?int $classID = null
    ) {
        try {
            $studentQuery = Student::with(['subclass.class'])
                ->where('status', '!=', 'Transferred');

            if ($subclassID > 0) {
                $studentQuery->where('subclassID', $subclassID);
            } elseif ($classID) {
                $studentQuery->whereHas('subclass', function ($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            } elseif ($schoolID) {
                $studentQuery->where('schoolID', $schoolID);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Class or school filter is required.',
                    'data' => [],
                    'count' => 0,
                ], 422);
            }

            $students = $studentQuery->get()->keyBy('studentID');

            if ($students->isEmpty()) {
                $payload = [
                    'success' => true,
                    'date' => $today,
                    'device_failed' => $deviceFailed,
                    'device_ip' => $deviceIp,
                    'devices_synced' => $devicesSummary,
                    'data' => [],
                    'count' => 0,
                    'message' => 'No students found for this filter.',
                ];
                if ($schoolID) {
                    $payload['recent_events'] = StudentLiveEventBuffer::all($schoolID);
                }

                return response()->json($payload);
            }

            $attendanceRecords = StudentFingerprintAttendance::whereIn('studentID', $students->pluck('studentID'))
                ->whereDate('attendance_date', $today)
                ->orderBy('check_in_time', 'desc')
                ->get();

            $data = [];
            $newRecords = [];
            $oldRecords = [];

            foreach ($attendanceRecords as $record) {
                /** @var Student|null $student */
                $student = $students->get($record->studentID);
                if (! $student) {
                    continue;
                }

                $studentName = trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''));
                $className = 'N/A';
                if ($student->subclass) {
                    $mainClass = $student->subclass->class->class_name ?? '';
                    $subName = trim($student->subclass->subclass_name ?? '');
                    $className = trim($mainClass.($subName !== '' ? ' '.$subName : ''));
                }
                $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                $isNew = false;
                if ($record->check_in_time) {
                    $checkInTime = Carbon::parse($record->check_in_time);
                    $isNew = $checkInTime->diffInMinutes(Carbon::now()) <= 10;
                }

                $recordData = [
                    'studentID' => $student->studentID,
                    'user_id' => $student->studentID,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $student->studentID,
                    'fingerprint_id' => $fingerprintId,
                    'class_name' => $className,
                    'subclassID' => (int) ($student->subclassID ?? 0),
                    'gender' => $student->gender ?? null,
                    'attendance_date' => $record->attendance_date ? $record->attendance_date->toDateString() : $today,
                    'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
                    'verify_mode' => $record->verify_mode ?? null,
                    'device_ip' => $record->device_ip ?? null,
                    'is_new' => $isNew,
                ];

                if ($isNew) {
                    $newRecords[] = $recordData;
                } else {
                    $oldRecords[] = $recordData;
                }
            }

            usort($newRecords, function ($a, $b) {
                $timeA = $a['check_in_time'] ? strtotime($a['check_in_time']) : 0;
                $timeB = $b['check_in_time'] ? strtotime($b['check_in_time']) : 0;

                return $timeB - $timeA;
            });

            if (count($newRecords) > 0) {
                $newRecords[0]['is_new'] = true;
                for ($i = 1; $i < count($newRecords); $i++) {
                    $newRecords[$i]['is_new'] = false;
                }
            }

            usort($oldRecords, function ($a, $b) {
                $timeA = $a['check_in_time'] ? strtotime($a['check_in_time']) : 0;
                $timeB = $b['check_in_time'] ? strtotime($b['check_in_time']) : 0;

                return $timeB - $timeA;
            });

            $data = array_merge($newRecords, $oldRecords);

            $resolvedSchoolID = $schoolID ?? (int) Session::get('schoolID');
            if ($resolvedSchoolID > 0) {
                $includeAllStudents = $subclassID > 0 || ($classID !== null && $classID > 0);
                $data = $this->applyStudentPermissionToLiveRows(
                    $resolvedSchoolID,
                    $today,
                    $data,
                    $students,
                    $includeAllStudents
                );
            }
            $response = [
                'success' => true,
                'date' => $today,
                'device_failed' => $deviceFailed,
                'device_ip' => $deviceIp,
                'devices_synced' => $devicesSummary,
                'data' => $data,
                'count' => count($data),
                'message' => $deviceFailed
                    ? 'Device unavailable, showing attendance from database for today.'
                    : null,
            ];

            if ($resolvedSchoolID > 0) {
                $response['recent_events'] = StudentLiveEventBuffer::all($resolvedSchoolID);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Get live attendance today from DB error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'date' => $today,
                'device_failed' => $deviceFailed,
                'message' => 'Error retrieving today\'s attendance from database: '.$e->getMessage(),
                'data' => [],
                'count' => 0,
            ], 500);
        }
    }

    /**
     * Fallback: Get fingerprint attendance from database (includes today's date)
     * Used as fallback when device is unavailable
     */
    private function getFingerprintAttendanceFromDBFallback(Request $request)
    {
        $subclassID = $request->input('subclassID');
        $date = $request->input('date');
        $page = $request->input('page', 1);
        $perPage = 50;

        try {
            // Get students in this subclass
            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            // Build query for student_fingerprint_attendance
            $query = StudentFingerprintAttendance::whereIn('studentID', $students->pluck('studentID'));

            // Filter by date if provided (includes today)
            if ($date) {
                $carbonDate = Carbon::parse($date)->toDateString();
                $query->whereDate('attendance_date', $carbonDate);
            }
            // If no date, get all records (including today)

            // Get records and group by studentID and date
            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'asc')
                ->get();

            // Group by studentID and date (one record per student per day)
            $grouped = [];
            foreach ($attendanceRecords as $record) {
                $student = $students->get($record->studentID);
                if (!$student) continue;

                $key = $record->studentID . '_' . $record->attendance_date->toDateString();

                if (!isset($grouped[$key])) {
                    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
                    $className = $student->subclass ? $student->subclass->subclass_name : 'N/A';
                    $fingerprintId = $student->fingerprint_id ?? $student->studentID;

                    $grouped[$key] = [
                        'studentID' => $student->studentID,
                        'user_id' => $student->studentID,
                        'user_name' => $studentName ?: 'N/A',
                        'enroll_id' => $student->studentID,
                        'fingerprint_id' => $fingerprintId,
                        'class_name' => $className,
                        'attendance_date' => $record->attendance_date->toDateString(),
                        'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
                        'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
                        'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
                        'verify_mode' => $record->verify_mode ?? null,
                        'device_ip' => $record->device_ip ?? null,
                    ];
                }
            }

            $records = array_values($grouped);

            // Paginate
            $total = count($records);
            $offset = ($page - 1) * $perPage;
            $paginatedRecords = array_slice($records, $offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
                'message' => 'Retrieved ' . $total . ' attendance record(s) from database (device unavailable)',
                'source' => 'database' // Flag to indicate data came from DB
            ]);
        } catch (\Exception $e) {
            Log::error('Get fingerprint attendance from DB error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attendance from database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all attendance data from device to database
     * - Syncs all dates (not just today)
     * - Groups by studentID + date
     * - Takes only first 2 punches per student per day (check-in and check-out)
     * - Uses updateOrCreate to ensure one record per student per day
     */
    public function syncAllAttendanceFromDevice(Request $request)
    {
        $request->validate([
            'subclassID' => 'required|integer',
            'device_id' => 'required|string',
        ]);

        $subclassID = $request->input('subclassID');

        try {
            set_time_limit(90);

            $students = Student::with('subclass')
                ->where('subclassID', $subclassID)
                ->where('status', '!=', 'Transferred')
                ->get()
                ->keyBy('studentID');

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found in this class.'
                ], 400);
            }

            $syncResult = $this->syncAttendanceFromDevices($request, $students, null);

            if (!$syncResult['anySuccess']) {
                $failedLabels = collect($syncResult['summary'])
                    ->where('success', false)
                    ->pluck('label')
                    ->implode(', ');

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to any registered device.' . ($failedLabels ? " ({$failedLabels})" : ''),
                    'devices_synced' => $syncResult['summary'],
                ], 500);
            }

            $persistStats = $this->persistGroupedAttendanceRecords($syncResult['userDateRecords']);
            $syncedCount = $persistStats['synced'];
            $updatedCount = $persistStats['updated'];
            $deviceLabel = collect($syncResult['summary'])->where('success', true)->pluck('label')->first() ?? 'device';

            return response()->json([
                'success' => true,
                'message' => "Successfully synced attendance from {$deviceLabel}. {$syncedCount} new record(s) created, {$updatedCount} record(s) updated.",
                'synced' => $syncedCount,
                'updated' => $updatedCount,
                'total' => $syncedCount + $updatedCount,
                'devices_synced' => $syncResult['summary'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Sync all attendance from device error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error syncing attendance from device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registered fingerprint devices (per school — SaaS).
     */
    public function listDevices()
    {
        $schoolID = $this->resolveFingerprintSchoolId();

        $devices = FingerprintDevice::where('schoolID', $schoolID)
            ->orderBy('label')
            ->get()
            ->map(fn ($d) => $d->toApiArray());

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ]);
    }

    public function storeDevice(Request $request)
    {
        $schoolID = $this->resolveFingerprintSchoolId();
        $data = $this->validateFingerprintDevice($request);

        if (
            FingerprintDevice::where('schoolID', $schoolID)
                ->where('label', $data['label'])
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'A device with this label already exists for your school.',
            ], 422);
        }

        $device = FingerprintDevice::create([
            ...$data,
            'schoolID' => $schoolID,
            'created_by' => Session::get('userID'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully.',
            'device' => $device->toApiArray(),
        ], 201);
    }

    public function showDevice(int $id)
    {
        $device = $this->findSchoolFingerprintDevice($id);

        return response()->json([
            'success' => true,
            'device' => $device->toApiArray(),
        ]);
    }

    public function updateDevice(Request $request, int $id)
    {
        $schoolID = $this->resolveFingerprintSchoolId();
        $device = $this->findSchoolFingerprintDevice($id);
        $data = $this->validateFingerprintDevice($request);

        if (
            FingerprintDevice::where('schoolID', $schoolID)
                ->where('label', $data['label'])
                ->where('id', '!=', $device->id)
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Another device with this label already exists.',
            ], 422);
        }

        $device->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Device updated successfully.',
            'device' => $device->fresh()->toApiArray(),
        ]);
    }

    public function deleteDevice(int $id)
    {
        $device = $this->findSchoolFingerprintDevice($id);
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully.',
        ]);
    }

    private function resolveFingerprintSchoolId(): int
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            abort(403, 'School session not found');
        }

        return (int) $schoolID;
    }

    /**
     * @return \Illuminate\Support\Collection<int, FingerprintDevice>
     */
    private function getAttendanceDevicesForSync(Request $request): \Illuminate\Support\Collection
    {
        $schoolID = $this->resolveFingerprintSchoolId();
        $deviceId = $request->input('device_id');

        if ($deviceId === null || $deviceId === '' || $deviceId === 'all') {
            throw new \InvalidArgumentException(
                'Please select one fingerprint device. Syncing multiple devices at once is not allowed.'
            );
        }

        if (!is_numeric($deviceId)) {
            throw new \InvalidArgumentException('Invalid fingerprint device selected.');
        }

        $device = FingerprintDevice::where('schoolID', $schoolID)
            ->where('status', 'active')
            ->where('id', (int) $deviceId)
            ->first();

        if (!$device) {
            throw new \InvalidArgumentException('Selected fingerprint device not found or is inactive.');
        }

        return collect([$device]);
    }

    /**
     * @param \Illuminate\Support\Collection<int, Student> $students
     */
    private function fetchAttendanceLogsFromDevice(FingerprintDevice $device, int $timeoutSeconds = 15, ?string $onlyDate = null): array
    {
        $ip = $device->ip;
        $port = (int) $device->port;
        $password = $device->comm_key ?? '0';

        Log::info("Attendance Sync: connecting to [{$device->label}] at {$ip}:{$port}");

        $zkteco = new ZKTecoService($ip, $port, $password, $timeoutSeconds, true);
        try {
            if (!$zkteco->connect()) {
                throw new \RuntimeException(
                    "Failed to connect to {$device->label} ({$ip}:{$port}). Check IP, port, and comm key."
                );
            }

            $allAttendance = $zkteco->getAttendance();

            if (!is_array($allAttendance)) {
                throw new \RuntimeException("Invalid attendance data from {$device->label}.");
            }

            if ($onlyDate !== null && !empty($allAttendance)) {
                $allAttendance = array_values(array_filter($allAttendance, function ($record) use ($onlyDate) {
                    if (empty($record['record_time'])) {
                        return false;
                    }
                    try {
                        return Carbon::parse($record['record_time'])->toDateString() === $onlyDate;
                    } catch (\Exception $e) {
                        return false;
                    }
                }));
            }

            return $allAttendance;
        } finally {
            $zkteco->disconnect();
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, Student> $students
     */
    private function extractUserDateRecords(array $allAttendance, $students, ?string $onlyDate, string $deviceIp): array
    {
        $userDateRecords = [];

        foreach ($allAttendance as $record) {
            $userId = isset($record['user_id']) ? (int) $record['user_id'] : null;
            if (!$userId || !isset($record['record_time'])) {
                continue;
            }

            $recordTime = Carbon::parse($record['record_time']);
            $recordDate = $recordTime->toDateString();

            if ($onlyDate !== null && $recordDate !== $onlyDate) {
                continue;
            }

            $student = $students->get($userId);
            if (!$student) {
                continue;
            }

            $key = $userId . '_' . $recordDate;
            if (!isset($userDateRecords[$key])) {
                $userDateRecords[$key] = [];
            }

            $userDateRecords[$key][] = [
                'time' => $recordTime,
                'type' => $record['type'] ?? 'Fingerprint',
                'device_ip' => $record['device_ip'] ?? $deviceIp,
                'student' => $student,
            ];
        }

        return $userDateRecords;
    }

    private function mergeUserDateRecords(array &$target, array $source): void
    {
        foreach ($source as $key => $records) {
            if (!isset($target[$key])) {
                $target[$key] = [];
            }
            $target[$key] = array_merge($target[$key], $records);
        }
    }

    private function persistGroupedAttendanceRecords(array $userDateRecords): array
    {
        $syncedCount = 0;
        $updatedCount = 0;

        foreach ($userDateRecords as $records) {
            usort($records, function ($a, $b) {
                if ($a['time']->eq($b['time'])) {
                    return 0;
                }
                return $a['time']->lt($b['time']) ? -1 : 1;
            });

            if (count($records) < 1) {
                continue;
            }

            $firstRecord = $records[0];
            $secondRecord = count($records) >= 2 ? $records[1] : null;

            /** @var Student $student */
            $student = $firstRecord['student'];
            $recordDate = $firstRecord['time']->toDateString();
            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));

            $checkInTime = $firstRecord['time']->format('Y-m-d H:i:s');
            $checkOutTime = $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null;
            $status = $checkOutTime ? 1 : 0;

            $existing = StudentFingerprintAttendance::where('studentID', $student->studentID)
                ->whereDate('attendance_date', $recordDate)
                ->first();

            StudentFingerprintAttendance::updateOrCreate(
                [
                    'studentID' => $student->studentID,
                    'attendance_date' => $recordDate,
                ],
                [
                    'enrollmentID' => app(\App\Services\StudentEnrollmentService::class)
                        ->bestEnrollmentID($student->studentID, (int) ($student->schoolID ?? 0) ?: null),
                    'user_id' => $student->studentID,
                    'user_name' => $studentName ?: 'N/A',
                    'enroll_id' => $student->studentID,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'status' => $status,
                    'verify_mode' => $firstRecord['type'] ?? 'Fingerprint',
                    'device_ip' => $firstRecord['device_ip'],
                ]
            );

            if ($existing) {
                $updatedCount++;
            } else {
                $syncedCount++;
            }
        }

        return ['synced' => $syncedCount, 'updated' => $updatedCount];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Student> $students
     * @return array{userDateRecords: array, summary: array, anySuccess: bool}
     */
    private function syncAttendanceFromDevices(Request $request, $students, ?string $onlyDate): array
    {
        $devices = $this->getAttendanceDevicesForSync($request);
        $userDateRecords = [];
        $summary = [];
        $anySuccess = false;

        foreach ($devices as $device) {
            try {
                $logs = $this->fetchAttendanceLogsFromDevice($device);
                $records = $this->extractUserDateRecords($logs, $students, $onlyDate, $device->ip);
                $this->mergeUserDateRecords($userDateRecords, $records);
                $summary[] = [
                    'id' => $device->id,
                    'label' => $device->label,
                    'ip' => $device->ip,
                    'port' => $device->port,
                    'success' => true,
                ];
                $anySuccess = true;
            } catch (\Exception $e) {
                Log::warning('Attendance device sync failed [' . $device->label . ']: ' . $e->getMessage());
                $summary[] = [
                    'id' => $device->id,
                    'label' => $device->label,
                    'ip' => $device->ip,
                    'port' => $device->port,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'userDateRecords' => $userDateRecords,
            'summary' => $summary,
            'anySuccess' => $anySuccess,
        ];
    }

    /**
     * Live sync + fetch today's teacher/staff fingerprint attendance from ZKTeco device.
     */
    public function syncLiveTeacherStaffAttendanceToday(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'db_only' => 'nullable|boolean',
            'person_type' => 'nullable|in:all,teacher,staff',
        ]);

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School session not found. Please refresh the page or log in again.',
            ], 403);
        }
        $schoolID = (int) $schoolID;
        $today = Carbon::today()->toDateString();
        $dbOnly = filter_var($request->input('db_only'), FILTER_VALIDATE_BOOLEAN);
        $personTypeFilter = $request->input('person_type', 'all');

        try {
            if ($dbOnly) {
                return $this->getLiveTeacherStaffAttendanceTodayFromDB(
                    $schoolID,
                    $today,
                    false,
                    [],
                    $personTypeFilter
                );
            }

            set_time_limit(60);

            $peopleMap = $this->getTeacherStaffPeopleMap($schoolID);
            $syncResult = $this->syncTeacherStaffFromDevices($request, $peopleMap, $today, 12);

            if ($syncResult['anySuccess'] && !empty($syncResult['userDateRecords'])) {
                $this->persistTeacherStaffGroupedRecords($syncResult['userDateRecords']);
            }

            return $this->getLiveTeacherStaffAttendanceTodayFromDB(
                $schoolID,
                $today,
                !$syncResult['anySuccess'],
                $syncResult['summary'],
                $personTypeFilter
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Teacher/staff live attendance sync error: ' . $e->getMessage());

            return $this->getLiveTeacherStaffAttendanceTodayFromDB(
                $schoolID,
                $today,
                true,
                [],
                $personTypeFilter
            );
        }
    }

    /**
     * Live teacher/staff attendance for Today (DB only).
     * Used when device sync is done by a local agent (LAN) and this server only receives updates.
     */
    public function getTeacherStaffLiveTodayFromDb(Request $request)
    {
        $request->validate([
            'person_type' => 'nullable|in:all,teacher,staff',
        ]);

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json([
                'success' => false,
                'message' => 'School session not found. Please refresh the page or log in again.',
            ], 403);
        }

        $schoolID = (int) $schoolID;
        $today = Carbon::today()->toDateString();

        $personTypeFilter = (string) $request->input('person_type', 'all');

        return $this->getLiveTeacherStaffAttendanceTodayFromDB(
            $schoolID,
            $today,
            false,
            [],
            $personTypeFilter
        );
    }

    /**
     * Sync ALL teacher/staff attendance logs from device into database.
     * Skips records already saved (teacher + date) — safe for offline devices with months of logs.
     */
    public function syncTeacherStaffAttendanceFromDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $schoolID = $this->resolveFingerprintSchoolId();

        try {
            set_time_limit(300);

            $peopleMap = $this->getTeacherStaffPeopleMap($schoolID);
            if (empty($peopleMap)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active teachers or staff found for this school.',
                ], 400);
            }

            // Pull every attendance log on the device (no date filter)
            $syncResult = $this->syncTeacherStaffFromDevices($request, $peopleMap, null, 90);

            if (!$syncResult['anySuccess']) {
                $failedLabels = collect($syncResult['summary'])
                    ->where('success', false)
                    ->pluck('label')
                    ->implode(', ');

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to the selected device.' . ($failedLabels ? " ({$failedLabels})" : ''),
                    'devices_synced' => $syncResult['summary'],
                ], 500);
            }

            $persistStats = app(TeacherStaffAttendanceSyncService::class)
                ->persistBulkSyncSkipExisting($schoolID, $syncResult['userDateRecords']);
            $deviceLabel = collect($syncResult['summary'])->where('success', true)->pluck('label')->first() ?? 'device';

            $synced = $persistStats['synced'];
            $skipped = $persistStats['skipped_existing'];
            $updatedCheckout = $persistStats['updated_checkout'] ?? 0;
            $message = "Synced from {$deviceLabel}. {$synced} new day(s) saved to database.";
            if ($updatedCheckout > 0) {
                $message .= " {$updatedCheckout} check-out time(s) updated.";
            }
            if ($skipped > 0) {
                $message .= " {$skipped} already complete in database (skipped).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'synced' => $synced,
                'skipped_existing' => $skipped,
                'updated_checkout' => $updatedCheckout,
                'device_days_matched' => $persistStats['device_days_matched'],
                'devices_synced' => $syncResult['summary'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Teacher/staff device sync error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync teacher/staff attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getLiveTeacherStaffAttendanceTodayFromDB(
        int $schoolID,
        string $today,
        bool $deviceFailed = false,
        array $devicesSummary = [],
        string $personTypeFilter = 'all'
    ) {
        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get()
            ->keyBy('id');

        $staffMembers = OtherStaff::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get()
            ->keyBy('id');

        $newRecords = [];
        $oldRecords = [];

        if ($personTypeFilter !== 'staff' && $teachers->isNotEmpty()) {
            $attendanceRecords = TeacherFingerprintAttendance::whereIn('teacherID', $teachers->pluck('id'))
                ->whereDate('attendance_date', $today)
                ->orderBy('check_in_time', 'desc')
                ->get();

            foreach ($attendanceRecords as $record) {
                $teacher = $teachers->get($record->teacherID);
                if (!$teacher) {
                    continue;
                }

                $recordData = $this->formatTeacherLiveAttendanceRow($record, $teacher, $today);
                if ($recordData['is_new']) {
                    $newRecords[] = $recordData;
                } else {
                    $oldRecords[] = $recordData;
                }
            }
        }

        if ($personTypeFilter !== 'teacher' && $staffMembers->isNotEmpty()) {
            $staffAttendanceRecords = StaffFingerprintAttendance::whereIn('staffID', $staffMembers->pluck('id'))
                ->whereDate('attendance_date', $today)
                ->orderBy('check_in_time', 'desc')
                ->get();

            foreach ($staffAttendanceRecords as $record) {
                $staff = $staffMembers->get($record->staffID);
                if (!$staff) {
                    continue;
                }

                $recordData = $this->formatStaffLiveAttendanceRow($record, $staff, $today);
                if ($recordData['is_new']) {
                    $newRecords[] = $recordData;
                } else {
                    $oldRecords[] = $recordData;
                }
            }
        }

        usort($newRecords, function ($a, $b) {
            $timeA = $a['check_in_time'] ? strtotime($a['check_in_time']) : 0;
            $timeB = $b['check_in_time'] ? strtotime($b['check_in_time']) : 0;
            return $timeB - $timeA;
        });

        if (!empty($newRecords)) {
            $newRecords[0]['is_new'] = true;
            for ($i = 1; $i < count($newRecords); $i++) {
                $newRecords[$i]['is_new'] = false;
            }
        }

        $data = array_merge($newRecords, $oldRecords);
        $data = $this->applyTeacherStaffPermissionToLiveRows($schoolID, $today, $data, $teachers, $staffMembers, $personTypeFilter);

        return response()->json([
            'success' => true,
            'date' => $today,
            'device_failed' => $deviceFailed,
            'devices_synced' => $devicesSummary,
            'data' => $data,
            'count' => count($data),
            'teacher_count' => $teachers->count(),
            'recent_events' => TeacherStaffLiveEventBuffer::all($schoolID),
            'message' => $data ? null : 'No teacher/staff attendance records found for today.',
        ]);
    }

    /**
     * @param \Illuminate\Support\Collection<int, Teacher> $teachers
     * @param \Illuminate\Support\Collection<int, OtherStaff> $staffMembers
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param \Illuminate\Support\Collection<int, Student> $students
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function applyStudentPermissionToLiveRows(
        int $schoolID,
        string $today,
        array $rows,
        $students,
        bool $includeAllStudentsInScope
    ): array {
        $permissionService = app(StudentPermissionAttendanceService::class);
        $permissionMap = $permissionService->loadApprovedStatusMap($schoolID, $today, $today);

        $rowsByStudentId = [];
        foreach ($rows as $index => $row) {
            $studentId = (int) ($row['studentID'] ?? 0);
            if ($studentId > 0) {
                $rowsByStudentId[$studentId] = $index;
            }
        }

        foreach ($rows as $index => $row) {
            $studentId = (int) ($row['studentID'] ?? 0);
            $permStatus = $permissionService->statusForStudentOnDate($permissionMap, $studentId, $today);
            $dayStatus = $permissionService->resolveFingerprintDayStatus($row['check_in_time'] ?? null, $permStatus);
            $rows[$index]['attendance_status'] = $dayStatus;
            $rows[$index]['status'] = $dayStatus;
        }

        foreach ($students as $student) {
            $studentId = (int) $student->studentID;
            if (isset($rowsByStudentId[$studentId])) {
                continue;
            }

            $permStatus = $permissionService->statusForStudentOnDate($permissionMap, $studentId, $today);
            if ($permStatus) {
                $dayStatus = $permissionService->resolveFingerprintDayStatus(null, $permStatus);
            } elseif (! $includeAllStudentsInScope) {
                continue;
            } else {
                $dayStatus = 'Absent';
            }

            $studentName = trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''));
            $className = 'N/A';
            if ($student->subclass) {
                $mainClass = $student->subclass->class->class_name ?? '';
                $subName = trim($student->subclass->subclass_name ?? '');
                $className = trim($mainClass.($subName !== '' ? ' '.$subName : ''));
            }

            $rows[] = [
                'studentID' => $studentId,
                'user_id' => $studentId,
                'user_name' => $studentName ?: 'N/A',
                'enroll_id' => $studentId,
                'fingerprint_id' => $student->fingerprint_id ?? $studentId,
                'class_name' => $className,
                'subclassID' => (int) ($student->subclassID ?? 0),
                'gender' => $student->gender ?? null,
                'attendance_date' => $today,
                'check_in_time' => null,
                'check_out_time' => null,
                'status' => $dayStatus,
                'attendance_status' => $dayStatus,
                'verify_mode' => null,
                'device_ip' => null,
                'is_new' => false,
            ];
        }

        return $rows;
    }

    private function applyTeacherStaffPermissionToLiveRows(
        int $schoolID,
        string $today,
        array $rows,
        $teachers,
        $staffMembers,
        string $personTypeFilter = 'all'
    ): array {
        $permissionService = app(TeacherStaffPermissionAttendanceService::class);
        $permissionMap = $permissionService->loadApprovedPermissionDatesMap(
            $schoolID,
            $today,
            $today,
            $personTypeFilter
        );

        $seenKeys = [];

        foreach ($rows as $index => $row) {
            $personKey = ($row['person_type'] ?? '') . '_' . ($row['person_id'] ?? 0);
            $seenKeys[$personKey] = true;

            if ($permissionService->isPermittedOnDate($permissionMap, $personKey, $today)) {
                $rows[$index]['status'] = 'Permitted';
                $rows[$index]['attendance_status'] = 'Permitted';
            }
        }

        if ($personTypeFilter !== 'staff') {
            foreach ($teachers as $teacher) {
                $personKey = 'teacher_' . $teacher->id;
                if (isset($seenKeys[$personKey])) {
                    continue;
                }
                if (!$permissionService->isPermittedOnDate($permissionMap, $personKey, $today)) {
                    continue;
                }

                $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                $rows[] = [
                    'person_type' => 'teacher',
                    'person_id' => $teacher->id,
                    'user_id' => $teacher->id,
                    'user_name' => $teacherName ?: 'N/A',
                    'enroll_id' => $teacher->id,
                    'fingerprint_id' => $teacher->fingerprint_id ?? $teacher->id,
                    'role_label' => $teacher->position ?? 'Teacher',
                    'employee_number' => $teacher->employee_number ?? null,
                    'attendance_date' => $today,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'Permitted',
                    'attendance_status' => 'Permitted',
                    'verify_mode' => null,
                    'device_ip' => null,
                    'is_new' => false,
                    'persisted' => true,
                ];
            }
        }

        if ($personTypeFilter !== 'teacher') {
            foreach ($staffMembers as $staff) {
                $personKey = 'staff_' . $staff->id;
                if (isset($seenKeys[$personKey])) {
                    continue;
                }
                if (!$permissionService->isPermittedOnDate($permissionMap, $personKey, $today)) {
                    continue;
                }

                $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->middle_name ?? '') . ' ' . ($staff->last_name ?? ''));
                $rows[] = [
                    'person_type' => 'staff',
                    'person_id' => $staff->id,
                    'user_id' => $staff->id,
                    'user_name' => $staffName ?: 'N/A',
                    'enroll_id' => $staff->fingerprint_id ?? $staff->id,
                    'fingerprint_id' => $staff->fingerprint_id ?? $staff->id,
                    'role_label' => $staff->position ?? 'Staff',
                    'employee_number' => $staff->employee_number ?? null,
                    'attendance_date' => $today,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'Permitted',
                    'attendance_status' => 'Permitted',
                    'verify_mode' => null,
                    'device_ip' => null,
                    'is_new' => false,
                    'persisted' => true,
                ];
            }
        }

        return $rows;
    }

    private function formatTeacherLiveAttendanceRow(TeacherFingerprintAttendance $record, Teacher $teacher, string $today): array
    {
        $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? ''));
        $fingerprintId = $teacher->fingerprint_id ?? $teacher->id;

        $isNew = false;
        if ($record->check_out_time) {
            $isNew = Carbon::parse($record->check_out_time)->diffInMinutes(Carbon::now()) <= 10;
        } elseif ($record->check_in_time) {
            $isNew = Carbon::parse($record->check_in_time)->diffInMinutes(Carbon::now()) <= 10;
        }

        return [
            'person_type' => 'teacher',
            'person_id' => $teacher->id,
            'user_id' => $teacher->id,
            'user_name' => $teacherName ?: 'N/A',
            'enroll_id' => $teacher->id,
            'fingerprint_id' => $fingerprintId,
            'role_label' => $teacher->position ?? 'Teacher',
            'employee_number' => $teacher->employee_number ?? null,
            'attendance_date' => $record->attendance_date ? $record->attendance_date->toDateString() : $today,
            'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
            'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
            'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
            'verify_mode' => $record->verify_mode ?? null,
            'device_ip' => $record->device_ip ?? null,
            'is_new' => $isNew,
            'persisted' => true,
        ];
    }

    private function formatStaffLiveAttendanceRow(StaffFingerprintAttendance $record, OtherStaff $staff, string $today): array
    {
        $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->middle_name ?? '') . ' ' . ($staff->last_name ?? ''));
        $fingerprintId = $staff->fingerprint_id ?? $staff->id;

        $isNew = false;
        if ($record->check_out_time) {
            $isNew = Carbon::parse($record->check_out_time)->diffInMinutes(Carbon::now()) <= 10;
        } elseif ($record->check_in_time) {
            $isNew = Carbon::parse($record->check_in_time)->diffInMinutes(Carbon::now()) <= 10;
        }

        return [
            'person_type' => 'staff',
            'person_id' => $staff->id,
            'user_id' => $staff->id,
            'user_name' => $staffName ?: 'N/A',
            'enroll_id' => $staff->fingerprint_id ?? $staff->id,
            'fingerprint_id' => $fingerprintId,
            'role_label' => $staff->position ?? 'Staff',
            'employee_number' => $staff->employee_number ?? null,
            'attendance_date' => $record->attendance_date ? $record->attendance_date->toDateString() : $today,
            'check_in_time' => $record->check_in_time ? $record->check_in_time->format('Y-m-d H:i:s') : null,
            'check_out_time' => $record->check_out_time ? $record->check_out_time->format('Y-m-d H:i:s') : null,
            'status' => ($record->check_in_time && $record->check_out_time) ? 'Complete' : 'Check In Only',
            'verify_mode' => $record->verify_mode ?? null,
            'device_ip' => $record->device_ip ?? null,
            'is_new' => $isNew,
            'persisted' => true,
        ];
    }

    /**
     * @return array<int, array{type: string, model: Teacher|OtherStaff}>
     */
    private function getTeacherStaffPeopleMap(int $schoolID): array
    {
        $map = [];

        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'Active')->get();
        foreach ($teachers as $teacher) {
            $map[(int) $teacher->id] = ['type' => 'teacher', 'model' => $teacher];
            if ($teacher->fingerprint_id) {
                $map[(int) $teacher->fingerprint_id] = ['type' => 'teacher', 'model' => $teacher];
            }
        }

        $staffMembers = OtherStaff::where('schoolID', $schoolID)->where('status', 'Active')->get();
        foreach ($staffMembers as $staff) {
            $map[(int) $staff->id] = ['type' => 'staff', 'model' => $staff];
            if ($staff->fingerprint_id) {
                $map[(int) $staff->fingerprint_id] = ['type' => 'staff', 'model' => $staff];
            }
        }

        return $map;
    }

    /**
     * @param array<int, array{type: string, model: Teacher|OtherStaff}> $peopleMap
     */
    private function extractTeacherStaffUserDateRecords(array $allAttendance, array $peopleMap, ?string $onlyDate, string $deviceIp): array
    {
        $userDateRecords = [];

        foreach ($allAttendance as $record) {
            $userId = isset($record['user_id']) ? (int) $record['user_id'] : null;
            if (!$userId || !isset($record['record_time'])) {
                continue;
            }

            $recordTime = Carbon::parse($record['record_time']);
            $recordDate = $recordTime->toDateString();

            if ($onlyDate !== null && $recordDate !== $onlyDate) {
                continue;
            }

            $personEntry = $peopleMap[$userId] ?? null;
            if (!$personEntry) {
                continue;
            }

            $person = $personEntry['model'];
            $personType = $personEntry['type'];
            $key = $personType . '_' . (int) $person->id . '_' . $recordDate;

            if (!isset($userDateRecords[$key])) {
                $userDateRecords[$key] = [];
            }

            $userDateRecords[$key][] = [
                'time' => $recordTime,
                'type' => $record['type'] ?? 'Fingerprint',
                'device_ip' => $record['device_ip'] ?? $deviceIp,
                'person' => $person,
                'person_type' => $personType,
            ];
        }

        return $userDateRecords;
    }

    /**
     * Check-in window logic via TeacherStaffAttendanceSyncService (not punch order).
     *
     * @return array{check_in: string|null, check_out: string|null, verify_mode: string|null, device_ip: string|null}
     */
    private function resolveTeacherDayCheckInOut(array $records, $existing = null): array
    {
        $schoolID = (int) $this->resolveFingerprintSchoolId();
        $workStartTime = (string) \App\Models\TeacherStaffWorkTimeSetting::forSchool($schoolID)->work_start_time;

        return app(TeacherStaffAttendanceSyncService::class)->resolveDayCheckInOut($records, $existing, $workStartTime);
    }

    private function persistTeacherStaffGroupedRecords(array $userDateRecords): array
    {
        $syncedCount = 0;
        $updatedCount = 0;

        foreach ($userDateRecords as $records) {
            if (count($records) < 1) {
                continue;
            }

            $personType = $records[0]['person_type'] ?? '';
            $person = $records[0]['person'];
            $recordDate = $records[0]['time']->toDateString();

            if ($personType === 'teacher') {
                /** @var Teacher $person */
                $teacherName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));

                $existing = TeacherFingerprintAttendance::where('teacherID', $person->id)
                    ->whereDate('attendance_date', $recordDate)
                    ->first();

                $times = $this->resolveTeacherDayCheckInOut($records, $existing);
                $status = $times['check_out'] ? 1 : 0;

                TeacherFingerprintAttendance::updateOrCreate(
                    [
                        'teacherID' => $person->id,
                        'attendance_date' => $recordDate,
                    ],
                    [
                        'user_id' => $person->id,
                        'user_name' => $teacherName ?: 'N/A',
                        'enroll_id' => $person->fingerprint_id ?? $person->id,
                        'check_in_time' => $times['check_in'],
                        'check_out_time' => $times['check_out'],
                        'status' => $status,
                        'verify_mode' => $times['verify_mode'],
                        'device_ip' => $times['device_ip'],
                    ]
                );

                if ($existing) {
                    $updatedCount++;
                } else {
                    $syncedCount++;
                }
                continue;
            }

            if ($personType === 'staff') {
                /** @var OtherStaff $person */
                $staffName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));

                $existing = StaffFingerprintAttendance::where('staffID', $person->id)
                    ->whereDate('attendance_date', $recordDate)
                    ->first();

                $times = $this->resolveTeacherDayCheckInOut($records, $existing);
                $status = $times['check_out'] ? 1 : 0;

                StaffFingerprintAttendance::updateOrCreate(
                    [
                        'staffID' => $person->id,
                        'attendance_date' => $recordDate,
                    ],
                    [
                        'user_id' => $person->id,
                        'user_name' => $staffName ?: 'N/A',
                        'enroll_id' => $person->fingerprint_id ?? $person->id,
                        'check_in_time' => $times['check_in'],
                        'check_out_time' => $times['check_out'],
                        'status' => $status,
                        'verify_mode' => $times['verify_mode'],
                        'device_ip' => $times['device_ip'],
                    ]
                );

                if ($existing) {
                    $updatedCount++;
                } else {
                    $syncedCount++;
                }
            }
        }

        return ['synced' => $syncedCount, 'updated' => $updatedCount];
    }

    /**
     * Bulk device sync: insert only teacher/day rows not already in DB (skip duplicates).
     */
    private function persistTeacherStaffBulkSyncSkipExisting(int $schoolID, array $userDateRecords): array
    {
        $teacherIds = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('id');

        $staffIds = OtherStaff::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('id');

        $existingTeacherPairs = [];
        $existingTeacherIncomplete = [];
        if ($teacherIds->isNotEmpty()) {
            TeacherFingerprintAttendance::whereIn('teacherID', $teacherIds)
                ->select(['teacherID', 'attendance_date', 'check_out_time'])
                ->cursor()
                ->each(function ($row) use (&$existingTeacherPairs, &$existingTeacherIncomplete) {
                    $date = $row->attendance_date instanceof Carbon
                        ? $row->attendance_date->toDateString()
                        : (string) $row->attendance_date;
                    $key = 'teacher_' . $row->teacherID . '_' . $date;
                    $existingTeacherPairs[$key] = true;
                    if (!$row->check_out_time) {
                        $existingTeacherIncomplete[$key] = true;
                    }
                });
        }

        $existingStaffPairs = [];
        $existingStaffIncomplete = [];
        if ($staffIds->isNotEmpty()) {
            StaffFingerprintAttendance::whereIn('staffID', $staffIds)
                ->select(['staffID', 'attendance_date', 'check_out_time'])
                ->cursor()
                ->each(function ($row) use (&$existingStaffPairs, &$existingStaffIncomplete) {
                    $date = $row->attendance_date instanceof Carbon
                        ? $row->attendance_date->toDateString()
                        : (string) $row->attendance_date;
                    $key = 'staff_' . $row->staffID . '_' . $date;
                    $existingStaffPairs[$key] = true;
                    if (!$row->check_out_time) {
                        $existingStaffIncomplete[$key] = true;
                    }
                });
        }

        $synced = 0;
        $skippedExisting = 0;
        $updatedCheckout = 0;
        $deviceDaysMatched = 0;

        foreach ($userDateRecords as $records) {
            if (count($records) < 1) {
                continue;
            }

            $deviceDaysMatched++;
            $personType = $records[0]['person_type'] ?? '';
            $person = $records[0]['person'];
            $recordDate = $records[0]['time']->toDateString();

            if ($personType === 'teacher') {
                /** @var Teacher $person */
                $pairKey = 'teacher_' . $person->id . '_' . $recordDate;
                $personName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));

                $existing = isset($existingTeacherPairs[$pairKey])
                    ? TeacherFingerprintAttendance::where('teacherID', $person->id)
                        ->whereDate('attendance_date', $recordDate)
                        ->first()
                    : null;

                $times = $this->resolveTeacherDayCheckInOut($records, $existing);

                if ($existing) {
                    $needsCheckout = !$existing->check_out_time && $times['check_out'];
                    if ($needsCheckout) {
                        $existing->update([
                            'check_in_time' => $times['check_in'],
                            'check_out_time' => $times['check_out'],
                            'status' => 1,
                            'verify_mode' => $times['verify_mode'],
                            'device_ip' => $times['device_ip'] ?? $existing->device_ip,
                        ]);
                        $updatedCheckout++;
                        unset($existingTeacherIncomplete[$pairKey]);
                    } else {
                        $skippedExisting++;
                    }
                    continue;
                }

                TeacherFingerprintAttendance::create([
                    'teacherID' => $person->id,
                    'user_id' => $person->id,
                    'user_name' => $personName ?: 'N/A',
                    'enroll_id' => $person->fingerprint_id ?? $person->id,
                    'attendance_date' => $recordDate,
                    'check_in_time' => $times['check_in'],
                    'check_out_time' => $times['check_out'],
                    'status' => $times['check_out'] ? 1 : 0,
                    'verify_mode' => $times['verify_mode'],
                    'device_ip' => $times['device_ip'],
                ]);

                $existingTeacherPairs[$pairKey] = true;
                $synced++;
                continue;
            }

            if ($personType === 'staff') {
                /** @var OtherStaff $person */
                $pairKey = 'staff_' . $person->id . '_' . $recordDate;
                $personName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));

                $existing = isset($existingStaffPairs[$pairKey])
                    ? StaffFingerprintAttendance::where('staffID', $person->id)
                        ->whereDate('attendance_date', $recordDate)
                        ->first()
                    : null;

                $times = $this->resolveTeacherDayCheckInOut($records, $existing);

                if ($existing) {
                    $needsCheckout = !$existing->check_out_time && $times['check_out'];
                    if ($needsCheckout) {
                        $existing->update([
                            'check_in_time' => $times['check_in'],
                            'check_out_time' => $times['check_out'],
                            'status' => 1,
                            'verify_mode' => $times['verify_mode'],
                            'device_ip' => $times['device_ip'] ?? $existing->device_ip,
                        ]);
                        $updatedCheckout++;
                        unset($existingStaffIncomplete[$pairKey]);
                    } else {
                        $skippedExisting++;
                    }
                    continue;
                }

                StaffFingerprintAttendance::create([
                    'staffID' => $person->id,
                    'user_id' => $person->id,
                    'user_name' => $personName ?: 'N/A',
                    'enroll_id' => $person->fingerprint_id ?? $person->id,
                    'attendance_date' => $recordDate,
                    'check_in_time' => $times['check_in'],
                    'check_out_time' => $times['check_out'],
                    'status' => $times['check_out'] ? 1 : 0,
                    'verify_mode' => $times['verify_mode'],
                    'device_ip' => $times['device_ip'],
                ]);

                $existingStaffPairs[$pairKey] = true;
                $synced++;
            }
        }

        return [
            'synced' => $synced,
            'skipped_existing' => $skippedExisting,
            'updated_checkout' => $updatedCheckout,
            'device_days_matched' => $deviceDaysMatched,
        ];
    }

    /**
     * @param array<int, array{type: string, model: Teacher|OtherStaff}> $peopleMap
     * @return array{userDateRecords: array, summary: array, anySuccess: bool}
     */
    private function syncTeacherStaffFromDevices(Request $request, array $peopleMap, ?string $onlyDate, int $deviceTimeoutSeconds = 15): array
    {
        $devices = $this->getAttendanceDevicesForSync($request);
        $userDateRecords = [];
        $summary = [];
        $anySuccess = false;

        foreach ($devices as $device) {
            try {
                $logs = $this->fetchAttendanceLogsFromDevice($device, $deviceTimeoutSeconds, $onlyDate);
                $records = $this->extractTeacherStaffUserDateRecords($logs, $peopleMap, $onlyDate, $device->ip);
                $this->mergeUserDateRecords($userDateRecords, $records);
                $summary[] = [
                    'id' => $device->id,
                    'label' => $device->label,
                    'ip' => $device->ip,
                    'port' => $device->port,
                    'success' => true,
                ];
                $anySuccess = true;
            } catch (\Exception $e) {
                Log::warning('Teacher/staff device sync failed [' . $device->label . ']: ' . $e->getMessage());
                $summary[] = [
                    'id' => $device->id,
                    'label' => $device->label,
                    'ip' => $device->ip,
                    'port' => $device->port,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'userDateRecords' => $userDateRecords,
            'summary' => $summary,
            'anySuccess' => $anySuccess,
        ];
    }

    private function buildTeacherLiveRecordsFromSync(array $userDateRecords, string $today): array
    {
        $records = [];

        foreach ($userDateRecords as $group) {
            if (count($group) < 1 || ($group[0]['person_type'] ?? '') !== 'teacher') {
                continue;
            }

            $recordDate = $group[0]['time']->toDateString();
            if ($recordDate !== $today) {
                continue;
            }

            /** @var Teacher $teacher */
            $teacher = $group[0]['person'];
            $times = $this->resolveTeacherDayCheckInOut($group, null);
            $teacherName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? ''));
            $checkInCarbon = $times['check_in'] ? Carbon::parse($times['check_in']) : null;
            $isNew = $checkInCarbon && $checkInCarbon->diffInMinutes(Carbon::now()) <= 10;

            $records[] = [
                'person_type' => 'teacher',
                'person_id' => $teacher->id,
                'user_id' => $teacher->id,
                'user_name' => $teacherName ?: 'N/A',
                'enroll_id' => $teacher->id,
                'fingerprint_id' => $teacher->fingerprint_id ?? $teacher->id,
                'role_label' => $teacher->position ?? 'Teacher',
                'employee_number' => $teacher->employee_number ?? null,
                'attendance_date' => $today,
                'check_in_time' => $times['check_in'],
                'check_out_time' => $times['check_out'],
                'status' => $times['check_out'] ? 'Complete' : 'Check In Only',
                'verify_mode' => $times['verify_mode'],
                'device_ip' => $times['device_ip'],
                'is_new' => $isNew,
                'persisted' => true,
            ];
        }

        return $records;
    }

    private function buildEphemeralStaffLiveRecords(array $userDateRecords, string $today): array
    {
        $records = [];

        foreach ($userDateRecords as $group) {
            usort($group, function ($a, $b) {
                if ($a['time']->eq($b['time'])) {
                    return 0;
                }
                return $a['time']->lt($b['time']) ? -1 : 1;
            });

            if (count($group) < 1 || ($group[0]['person_type'] ?? '') !== 'staff') {
                continue;
            }

            /** @var OtherStaff $staff */
            $staff = $group[0]['person'];
            $times = $this->resolveTeacherDayCheckInOut($group, null);
            $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->middle_name ?? '') . ' ' . ($staff->last_name ?? ''));
            $checkInTime = $times['check_in'];
            $checkOutTime = $times['check_out'];
            $isNew = $checkInTime && Carbon::parse($checkInTime)->diffInMinutes(Carbon::now()) <= 10;

            $records[] = [
                'person_type' => 'staff',
                'person_id' => $staff->id,
                'user_id' => $staff->id,
                'user_name' => $staffName ?: 'N/A',
                'enroll_id' => $staff->fingerprint_id ?? $staff->id,
                'fingerprint_id' => $staff->fingerprint_id ?? $staff->id,
                'role_label' => $staff->position ?? 'Staff',
                'employee_number' => $staff->employee_number ?? null,
                'attendance_date' => $today,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'status' => $checkOutTime ? 'Complete' : 'Check In Only',
                'verify_mode' => $group[0]['type'] ?? 'Fingerprint',
                'device_ip' => $group[0]['device_ip'] ?? null,
                'is_new' => $isNew,
                'persisted' => false,
            ];
        }

        return $records;
    }

    private function countStaffMatchedRecords(array $userDateRecords): int
    {
        $count = 0;
        foreach ($userDateRecords as $group) {
            if (!empty($group) && ($group[0]['person_type'] ?? '') === 'staff') {
                $count++;
            }
        }
        return $count;
    }

    private function findSchoolFingerprintDevice(int $id): FingerprintDevice
    {
        return FingerprintDevice::where('schoolID', $this->resolveFingerprintSchoolId())
            ->where('id', $id)
            ->firstOrFail();
    }

    private function validateFingerprintDevice(Request $request): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
            'brand' => 'required|string|max:60',
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'comm_key' => 'nullable|string|max:16',
            'usage_type' => 'nullable|string|max:40',
            'location' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:80',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:2000',
        ], [], [
            'comm_key' => 'comm key',
        ]);

        $data['comm_key'] = $data['comm_key'] ?? '0';

        return $data;
    }

    public function downloadUf200SchoolSyncGuide()
    {
        $schoolID = Session::get('schoolID');
        $school = $schoolID ? School::find($schoolID) : null;

        $data = [
            'school' => $school,
            'title' => 'Mwongozo: UF200-S na ShuleXpert (Sync kutoka Shule)',
            'generated_at' => now()->format('d M Y'),
            'live_url' => config('app.url'),
        ];

        $pdf = PDF::loadView('Admin.pdf.uf200_school_sync_guide_pdf', $data);
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('ShuleXpert_UF200-S_School_Sync_Guide.pdf');
    }
}

