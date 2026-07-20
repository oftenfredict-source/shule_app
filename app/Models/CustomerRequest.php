<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRequest extends Model
{
    protected $table = 'customer_requests';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }

        $this->forceFill([
            'is_read' => true,
            'read_at' => now(),
        ])->save();
    }
}
