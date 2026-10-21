<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectNote extends Model
{
    protected $table = 'subject_notes';
    protected $primaryKey = 'noteID';

    protected $fillable = [
        'schoolID',
        'academic_yearID',
        'subjectID',
        'class_subjectID',
        'teacherID',
        'level',
        'title',
        'kind',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'is_read_by_admin',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_read_by_admin' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }

    public function subject()
    {
        return $this->belongsTo(SchoolSubject::class, 'subjectID', 'subjectID');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subjectID', 'class_subjectID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(ParentSubjectNoteNotification::class, 'noteID', 'noteID');
    }

    public static function unreadAdminCounts(int $schoolID): array
    {
        $empty = ['note' => 0, 'past_paper' => 0];
        if ($schoolID < 1 || !\Illuminate\Support\Facades\Schema::hasTable('subject_notes')) {
            return $empty;
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('subject_notes', 'is_read_by_admin')) {
            return $empty;
        }

        $base = static::where('schoolID', $schoolID)->where('is_read_by_admin', false);
        if (\Illuminate\Support\Facades\Schema::hasColumn('subject_notes', 'kind')) {
            return [
                'note' => (clone $base)->where('kind', 'note')->count(),
                'past_paper' => (clone $base)->where('kind', 'past_paper')->count(),
            ];
        }

        return ['note' => $base->count(), 'past_paper' => 0];
    }
}
