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
        'login_status',
        'suspension_reason',
    ];

    protected $casts = [
        'is_under_organization' => 'boolean',
        'enable_social_media' => 'boolean',
        'social_media' => 'array',
    ];

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


