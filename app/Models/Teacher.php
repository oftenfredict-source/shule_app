<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * TANGE (Teachers Particular) fields used for registration completeness.
     * Keys = DB columns; values = English labels.
     */
    public const TANGE_REQUIRED_FIELDS = [
        'first_name' => 'Teacher / Officer Name (First)',
        'last_name' => 'Teacher / Officer Name (Last)',
        'gender' => 'Sex',
        'employee_number' => 'Check Number (C/N)',
        'tsc_number' => 'TSC Number (TSC/N)',
        'establishment_rank' => 'Establishment Rank',
        'salary_scale' => 'Salary Scale',
        'date_of_birth' => 'Date of Birth',
        'date_hired' => 'Date of Employment',
        'date_confirmed' => 'Date of Confirmation',
        'date_current_rank' => 'Date of Current Rank',
        'date_of_retirement' => 'Date of Retirement',
        'qualification' => 'Highest Education Level',
        'specialization' => 'Subjects Studied',
        'subjects_teaching' => 'Subjects Teaching',
        'position' => 'Position of Authority',
        'work_station' => 'Work Station',
        'address' => 'Place of Residence',
        'phone_number' => 'Phone Number',
    ];

    protected $fillable = [
        'id',
        'schoolID',
        'first_name',
        'middle_name',
        'image',
        'signature',
        'last_name',
        'gender',
        'national_id',
        'employee_number',
        'tsc_number',
        'qualification',
        'specialization',
        'subjects_teaching',
        'experience',
        'date_of_birth',
        'date_hired',
        'date_confirmed',
        'date_current_rank',
        'date_of_retirement',
        'address',
        'work_station',
        'email',
        'phone_number',
        'bank_account_number',
        'position',
        'establishment_rank',
        'salary_scale',
        'status',
        'fingerprint_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_hired' => 'date',
        'date_confirmed' => 'date',
        'date_current_rank' => 'date',
        'date_of_retirement' => 'date',
    ];

    protected $appends = [
        'full_name',
        'age',
        'years_of_experience',
        'tange_completion_percent',
        'tange_missing_fields',
        'retirement_alert',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        try {
            return Carbon::parse($this->date_of_birth)->age;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getYearsOfExperienceAttribute(): ?int
    {
        if (!$this->date_hired) {
            return null;
        }
        try {
            return Carbon::parse($this->date_hired)->diffInYears(Carbon::now());
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getTangeCompletionPercentAttribute(): int
    {
        $total = count(self::TANGE_REQUIRED_FIELDS);
        if ($total === 0) {
            return 100;
        }
        $filled = 0;
        foreach (array_keys(self::TANGE_REQUIRED_FIELDS) as $field) {
            $value = $this->{$field};
            if ($value !== null && trim((string) $value) !== '') {
                $filled++;
            }
        }
        return (int) round(($filled / $total) * 100);
    }

    public function getTangeMissingFieldsAttribute(): array
    {
        $missing = [];
        foreach (self::TANGE_REQUIRED_FIELDS as $field => $label) {
            $value = $this->{$field};
            if ($value === null || trim((string) $value) === '') {
                $missing[] = $label;
            }
        }
        return $missing;
    }

    /**
     * Alert when retirement is within the next 6 months (or overdue).
     */
    public function getRetirementAlertAttribute(): ?array
    {
        if (!$this->date_of_retirement) {
            return null;
        }
        try {
            $retire = Carbon::parse($this->date_of_retirement)->startOfDay();
            $now = Carbon::now()->startOfDay();
            $days = $now->diffInDays($retire, false);
            if ($days < 0) {
                return [
                    'level' => 'danger',
                    'message' => 'Retirement date has passed (' . $retire->format('d M Y') . ').',
                    'days' => $days,
                ];
            }
            if ($days <= 183) {
                return [
                    'level' => 'warning',
                    'message' => 'Retirement approaching on ' . $retire->format('d M Y') . ' (' . $days . ' days).',
                    'days' => $days,
                ];
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'teacherID', 'id');
    }

    public function subclasses()
    {
        return $this->hasMany(Subclass::class, 'teacherID', 'id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'teacherID', 'id');
    }
}
