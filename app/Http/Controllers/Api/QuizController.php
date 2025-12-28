<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Http\Requests\StoreQuizQuestionRequest;
use App\Http\Requests\UpdateQuizQuestionRequest;
use App\Models\QuizScore;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    public function getQuizQuestionsDistinct($classroom_id)
    {
        $quizzes = QuizQuestion::query()
            ->where('classroom_id', $classroom_id)
            ->select('classroom_id', 'quiz_title', 'created_at', 'quiz_code', 'due_date')
            ->distinct()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($quizzes);
    }

    public function getQuizQuestions($classroom_id, $created_at)
    {
        $quizz = QuizQuestion::query()
            ->where('classroom_id', $classroom_id)
            ->where('created_at', $created_at)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($quizz);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuizQuestionRequest $request)
    {
        $quizTitle = $request->quiz_title;
        $classroomId = $request->classroom_id;
        $createdBy = $request->created_by;
        $dueDate = $request->due_date;

        $questions = $request->questions;

        $quizCode = now()->format('YmdHis');


        foreach ($questions as $q) {
            QuizQuestion::create([
                'classroom_id'     => $classroomId,
                'created_by'       => $createdBy,
                'quiz_title'       => $quizTitle,
                'quiz_code'        => $quizCode,
                'questions_text'   => $q['questions_text'],
                'options'          => json_encode($q['options']),
                'correct_answer'   => $q['correct_answer'],
                'difficulty_level' => $q['difficulty_level'],
                'due_date'         => $dueDate
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz saved successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuizQuestion $quiz)
    {
        return response()->json([
            'id'               => $quiz->id,
            'classroom_id'     => $quiz->classroom_id,
            'created_by'       => $quiz->created_by,
            'questions_text'   => $quiz->questions_text,
            'options'          => json_decode($quiz->options, true),
            'correct_answer'   => $quiz->correct_answer,
            'difficulty_level' => $quiz->difficulty_level,
            'quiz_title'       => $quiz->quiz_title,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuizQuestionRequest $request, $classroom_id)
    {
        $quizTitle = $request->quiz_title;
        $questions = $request->questions;
        $due_date = $request->due_date;

        if (!$questions || !is_array($questions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No questions provided'
            ], 400);
        }

        foreach ($questions as $q) {
            // Update existing question
            if (isset($q['id'])) {
                $question = QuizQuestion::find($q['id']);
                if ($question) {
                    $question->update([
                        'quiz_title'       => $quizTitle,
                        'questions_text'   => $q['questions_text'],
                        'options'          => json_encode($q['options']),
                        'correct_answer'   => $q['correct_answer'],
                        'difficulty_level' => $q['difficulty_level'],
                        'due_date'         => $due_date
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz updated successfully'
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuizQuestion $quizQuestion)
    {
        //
    }


    public function destroyAll($classroom_id, $created_at)
    {

        QuizQuestion::where('classroom_id', $classroom_id)
            ->where('created_at', $created_at)
            ->delete();

        return response()->json(['message' => 'Quiz deleted successfully'], 200);
    }
}
