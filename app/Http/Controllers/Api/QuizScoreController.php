<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\QuizScore;
use Illuminate\Http\Request;

class QuizScoreController extends Controller
{


    public function index(Request $request)
    {
        // Optionally filter by classroom or quiz_code if provided
        $query = QuizScore::query()
            ->with([
                'student:id,name,email',
                'classroom:id,class_name,subject,section_code',
            ]);

        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        if ($request->has('quiz_code')) {
            $query->where('quiz_code', $request->quiz_code);
        }

        $scores = $query->orderBy('created_at', 'desc')->get();

        // Return formatted JSON
        return response()->json([
            'status' => 'success',
            'count' => $scores->count(),
            'data' => $scores,
        ]);
    }


    public function submitQuiz(Request $request, $classroomID)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'answers'    => 'required|array',
            'quiz_code'  => 'required|string',
            'score'      => 'required|numeric',
            'created_at' => 'required|date',
        ]);

        // ✅ Check if the user already submitted
        $existingScore = QuizScore::where('student_id', $request->student_id)
            ->where('quiz_code', $request->quiz_code)
            ->where('classroom_id', $classroomID)
            ->first();

        if ($existingScore) {
            return response()->json([
                'message' => 'Already submitted',
                'already_submitted' => true,
                'score' => $existingScore,
            ], 200);
        }

        $questions = QuizQuestion::where('classroom_id', $classroomID)
            ->where('created_at', $request->created_at)
            ->get();


        $correctCount = 0;
        foreach ($questions as $question) {
            if (
                isset($request->answers[$question->id]) &&
                $request->answers[$question->id] == $question->correct_answer
            ) {
                $correctCount++;
            }
        }

        $score = QuizScore::create([
            'classroom_id'    => $classroomID,
            'student_id'      => $request->student_id,
            'quiz_code'       => $request->quiz_code,
            'score'           => $request->score,
            'total_questions' => $questions->count(),
            'correct_answers' => $correctCount,
            'answers'         => json_encode($request->answers),
        ]);

        return response()->json([
            'message' => 'Quiz submitted successfully!',
            'already_submitted' => false,
            'score'   => $score
        ]);
    }

    public function getUserQuiz(Request $request, $classroomID, $quiz_code)
    {
        $quizScore = QuizScore::where('student_id', $request->student_id)
            ->where('classroom_id', $classroomID)
            ->where('quiz_code', $quiz_code)
            ->first();

        if (!$quizScore) {
            return response()->json(['already_submitted' => false]);
        }

        return response()->json([
            'already_submitted' => true,
            'score' => $quizScore,
        ]);
    }


    public function getScoresByClassroom($classroomId)
    {
        // Get all quiz scores for that classroom, with related student info
        $scores = QuizScore::with(['student:id,name,email'])
            ->where('classroom_id', $classroomId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($scores->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'No quiz scores found for this classroom.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'classroom_id' => $classroomId,
            'total_records' => $scores->count(),
            'data' => $scores,
        ]);
    }
}
