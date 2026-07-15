<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeBreakdown extends Model
{
    use HasFactory;

    protected $table = 'fee_breakdowns';
    protected $primaryKey = 'breakdownID';

    protected $fillable = [
        'feeID',
        'item_name',
        'amount',
        'display_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'display_order' => 'integer',
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class, 'feeID', 'feeID');
    }
}
