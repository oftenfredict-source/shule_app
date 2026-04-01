<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $table = 'payment_records';
    protected $primaryKey = 'recordID';

    protected $fillable = [
        'paymentID',
        'feeID',
        'paid_amount',
        'reference_number',
        'payment_date',
        'payment_source',
        'payment_by',
        'bank_name',
        'notes',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'payment_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'paymentID', 'paymentID');
    }
}
