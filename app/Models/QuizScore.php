<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizScore extends Model
{
    /** @use HasFactory<\Database\Factories\QuizScoreFactory> */
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'student_id',
        'quiz_code',
        'score',
        'total_questions',
        'correct_answers',
        'answers',
    ];
    protected $casts = [
        'answers' => 'array',
        'score' => 'float',
    ];



    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
