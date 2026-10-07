<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $table = 'schools';
    protected $primaryKey = 'schoolID';

    protected $fillable = [
        'school_name',
        'registration_number',
        'exam_center_number',
        'school_type',
        'ownership',
        'region',
        'district',
        'ward',
        'village',
        'address',
        'email',
        'phone',
        'established_year',
        'school_logo',
        'is_under_organization',
        'organization_name',
        'organization_logo',
        'enable_social_media',
        'social_media',
        'school_stamp',
        'school_signature',
        'character_categories_limit',
        'status',
        'environment',
        'two_factor_enabled',
        'must_change_default_password',
        'senderID',
        'sms_enabled',
        'student_portal_enabled',
        'login_status',
        'suspension_reason',
    ];

    protected $casts = [
        'is_under_organization' => 'boolean',
        'enable_social_media' => 'boolean',
        'sms_enabled' => 'boolean',
        'student_portal_enabled' => 'boolean',
        'social_media' => 'array',
    ];

    /**
     * Whether outbound SMS is allowed for this school.
     * Defaults to true when the column is missing/null (pre-migration safety).
     */
    public function isSmsEnabled(): bool
    {
        if ($this->sms_enabled === null) {
            return true;
        }

        return (bool) $this->sms_enabled;
    }

    public function isStudentPortalEnabled(): bool
    {
        return (bool) $this->student_portal_enabled;
    }

    public function socialMediaLinks(): array
    {
        if (!$this->enable_social_media) {
            return [];
        }

        $items = is_array($this->social_media) ? $this->social_media : [];
        $platforms = \App\Support\SchoolSocialMedia::platforms();
        $links = [];

        foreach ($items as $item) {
            $platform = $item['platform'] ?? '';
            $value = trim((string) ($item['value'] ?? ''));
            if (!$platform || $value === '' || !isset($platforms[$platform])) {
                continue;
            }
            $meta = $platforms[$platform];
            $links[] = [
                'platform' => $platform,
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'color' => $meta['color'] ?? '#940000',
                'href' => \App\Support\SchoolSocialMedia::href($platform, $value),
            ];
        }

        return $links;
    }

    public function phoneNumbers(): array
    {
        $numbers = [];
        $stored = $this->getAttribute('phones');
        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }
        if (is_array($stored)) {
            foreach ($stored as $number) {
                $number = trim((string) $number);
                if ($number !== '') {
                    $numbers[] = $number;
                }
            }
        }

        $primary = trim((string) ($this->phone ?? ''));
        if ($primary !== '') {
            array_unshift($numbers, $primary);
        }

        $unique = [];
        foreach ($numbers as $number) {
            $key = preg_replace('/\s+/', '', $number);
            if (!isset($unique[$key])) {
                $unique[$key] = $number;
            }
        }

        return array_values($unique);
    }

    public function formattedPhones(): string
    {
        return implode(' / ', $this->phoneNumbers());
    }

    public static function imageToDataUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);
        if (str_starts_with($path, 'data:image')) {
            return $path;
        }

        if (preg_match('#^https?://#i', $path)) {
            $urlPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($urlPath) && $urlPath !== '' ? $urlPath : $path;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        $parent = dirname(base_path());
        $possibilities = [
            public_path($relative),
            public_path('uploads/' . $relative),
            base_path($relative),
            base_path('public/' . $relative),
            $parent . '/public_html/' . $relative,
            $parent . '/public_html/uploads/' . $relative,
            base_path('../public_html/' . $relative),
        ];

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $possibilities[] = $docRoot . '/' . $relative;
            $possibilities[] = $docRoot . '/uploads/' . $relative;
        }

        if (!empty($_SERVER['SCRIPT_FILENAME'])) {
            $possibilities[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $relative;
        }

        foreach ($possibilities as $fullPath) {
            if (@file_exists($fullPath) && is_file($fullPath)) {
                try {
                    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png');
                    if ($ext === 'jpg') {
                        $ext = 'jpeg';
                    }
                    if ($ext === 'svg') {
                        $ext = 'svg+xml';
                    }
                    return 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fullPath));
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return null;
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'schoolID', 'schoolID');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'schoolID', 'schoolID');
    }

    public function schoolSubjects()
    {
        return $this->hasMany(SchoolSubject::class, 'schoolID', 'schoolID');
    }

    public function parents()
    {
        return $this->hasMany(ParentModel::class, 'schoolID', 'schoolID');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'schoolID', 'schoolID');
    }

    public function combies()
    {
        return $this->hasMany(Combie::class, 'schoolID', 'schoolID');
    }

    public function examinations()
    {
        return $this->hasMany(Examination::class, 'schoolID', 'schoolID');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class, 'schoolID', 'schoolID');
    }
}


