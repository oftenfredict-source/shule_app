<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDamage extends Model
{
    use HasFactory;

    protected $table = 'book_damages';
    protected $primaryKey = 'damageID';

    protected $fillable = [
        'bookID',
        'studentID',
        'teacherID',
        'staffID',
        'damaged_by',
        'description',
        'status',
        'payment_status',
        'payment_method',
        'payment_amount',
        'reported_date',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'status' => 'string',
        'damaged_by' => 'string',
        'payment_status' => 'string',
        'payment_method' => 'string',
        'payment_amount' => 'decimal:2',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'bookID', 'bookID');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'studentID', 'studentID');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacherID', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(OtherStaff::class, 'staffID', 'id');
    }
}
