<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassEnrollment extends Model
{
    /** @use HasFactory<\Database\Factories\ClassEnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'classroom_id',
        'status'
    ];
}
