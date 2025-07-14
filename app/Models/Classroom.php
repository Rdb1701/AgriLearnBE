<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    /** @use HasFactory<\Database\Factories\ClassroomFactory> */
    use HasFactory;

  protected $fillable = [
        'instructor_id',
        'class_name',
        'subject',
        'section_code'
    ];
}
