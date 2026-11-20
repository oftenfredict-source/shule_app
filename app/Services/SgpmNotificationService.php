<?php

namespace App\Services;

use App\Models\SgpmNotification;
use App\Models\User;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\School;
use App\Services\SmsService;

class SgpmNotificationService {
    protected $smsService;

    public function __construct() {
        $this->smsService = new SmsService();
    }

    /**
     * Notify a specific user via DB and SMS
     */
    public function notify($userId, $title, $message, $link, $type) {
        // 1. Database Notification
        SgpmNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
        ]);

        // 2. SMS Notification
        $user = User::find($userId);
        if ($user) {
            $phone = $this->getUserPhone($user);
            if ($phone) {
                $this->smsService->sendSms($phone, $message, null, $user->schoolID ?? null);
            }
        }
    }

    /**
     * Notify Admin(s) and also send SMS to school phone
     */
    public function notifyAdmin($title, $message, $link, $schoolId) {
        // The users table has no schoolID column. A school's Admin login is the user whose name is the
        // school's registration number (same link the Admin login uses).
        $schoolRecord = School::find($schoolId);
        $admins = $schoolRecord && $schoolRecord->registration_number
            ? User::where('name', $schoolRecord->registration_number)->where('user_type', 'Admin')->get()
            : collect();
        foreach($admins as $admin) {
            // Add DB notification for each admin
            SgpmNotification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => 'General',
            ]);
        }

        // Send SMS to School Phone Number
        $school = School::find($schoolId);
        if ($school && $school->phone) {
            $this->smsService->sendSms($school->phone, $message, null, $schoolId);
        }
    }

    protected function getUserPhone($user) {
        $teacher = Teacher::where('employee_number', $user->name)->first();
        if ($teacher) return $teacher->phone_number;
        
        $staff = OtherStaff::where('employee_number', $user->name)->first();
        if ($staff) return $staff->phone_number;

        return null;
    }
}
