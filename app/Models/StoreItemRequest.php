<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItemRequest extends Model
{
    protected $table = 'store_item_requests';
    protected $primaryKey = 'requestID';

    protected $fillable = [
        'schoolID',
        'requester_type',
        'requester_id',
        'requester_name',
        'status',
        'is_read_by_operator',
        'issues',
        'items_summary',
        'notes',
        'expected_return_date',
        'requested_by',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'is_read_by_operator' => 'boolean',
        'issues' => 'array',
        'expected_return_date' => 'date',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
}
