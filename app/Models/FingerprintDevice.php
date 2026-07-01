<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintDevice extends Model
{
    use HasFactory;

    protected $table = 'fingerprint_devices';

    protected $fillable = [
        'schoolID',
        'label',
        'brand',
        'ip',
        'port',
        'comm_key',
        'usage_type',
        'location',
        'serial_number',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'port' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'schoolID' => $this->schoolID,
            'label' => $this->label,
            'brand' => $this->brand,
            'ip' => $this->ip,
            'port' => $this->port,
            'comm_key' => $this->comm_key,
            'usage_type' => $this->usage_type,
            'location' => $this->location,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
