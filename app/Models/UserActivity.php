<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_log_id',
        'url',
        'method',
        'route_name',
        'payload',
    ];

    protected $appends = ['friendly_description'];

    public function log()
    {
        return $this->belongsTo(UserLog::class, 'user_log_id');
    }

    public function getFriendlyDescriptionAttribute()
    {
        $url = $this->url;
        $method = strtoupper($this->method);

        // Common Mappings
        $mappings = [
            'AdminDashboard' => 'Entered Dashboard',
            'super-admin/dashboard' => 'Entered Super Admin Dashboard',
            'manageClasses' => 'Managing Classes',
            'manageSubjects' => 'Managing Subjects',
            'save_school_subject' => 'Saved a School Subject',
            'visitor-notifications-count' => 'System Check: Visitor Notifications',
            'exam-paper-notifications-count' => 'System Check: Exam Paper Notifications',
            'get-recent-visitors' => 'Viewing Recent Visitors',
            'get-recent-exam-paper-notifications' => 'Viewing Recent Exam Notifications',
            'auth' => 'Attempted Login',
            'logout' => 'Logged Out',
            'system-logs' => 'Viewing Security Logs',
            'register-school' => 'Registering a School',
            'view-schools' => 'Viewing School List',
        ];

        foreach ($mappings as $key => $desc) {
            if (str_contains($url, $key)) {
                return $desc;
            }
        }

        // Generic logic based on URL structure
        $parts = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
        $lastPart = end($parts);
        
        if ($method === 'POST') return 'Submitted data to ' . $lastPart;
        if ($method === 'DELETE') return 'Deleted record from ' . $lastPart;
        
        return 'Accessed ' . ($lastPart ?: 'Home');
    }
}
