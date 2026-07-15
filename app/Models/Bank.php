<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'banks';
    protected $primaryKey = 'bankID';

    protected $fillable = [
        'bank_name',
        'bank_code',
        'status',
    ];

    public function schoolBankAccounts()
    {
        return $this->hasMany(SchoolBankAccount::class, 'bankID', 'bankID');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
