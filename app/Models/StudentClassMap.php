<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassMap extends Model
{
    use HasFactory;

    protected $table = 'student_class_map';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'class_id',
        'is_primary',
        'created_at',
    ];
}
