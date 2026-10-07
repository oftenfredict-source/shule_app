<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBoardDocument extends Model
{
    use HasFactory;

    protected $table = 'school_board_documents';
    protected $primaryKey = 'documentID';

    protected $fillable = [
        'schoolID',
        'meetingID',
        'resolutionID',
        'committeeID',
        'title',
        'category',
        'description',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'status',
        'uploaded_by',
        'uploaded_by_name',
    ];

    public function meeting()
    {
        return $this->belongsTo(SchoolBoardMeeting::class, 'meetingID', 'meetingID');
    }

    public function resolution()
    {
        return $this->belongsTo(SchoolBoardResolution::class, 'resolutionID', 'resolutionID');
    }

    public function committee()
    {
        return $this->belongsTo(SchoolBoardCommittee::class, 'committeeID', 'committeeID');
    }

    public function getFileSizeLabelAttribute(): string
    {
        $bytes = (int) ($this->file_size ?? 0);
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
