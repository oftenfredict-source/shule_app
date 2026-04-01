<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeAgreement extends Model
{
    use HasFactory;

    protected $table = 'fee_agreements';
    protected $primaryKey = 'agreementID';

    protected $fillable = [
        'schoolID',
        'studentID',
        'feeID',
        'academic_yearID',
        'agreed_amount',
        'description',
        'status',
    ];

    protected $casts = [
        'agreed_amount' => 'decimal:2',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }
}
