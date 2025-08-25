<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\QuizQuestionFactory> */
    use HasFactory;


     protected $fillable = [
        'classroom_id',
        'created_by',
        'questions_text',
        'options',
        'correct_answer',
        'difficulty_level',
        'quiz_title',
        'created_at'
    ];
}
