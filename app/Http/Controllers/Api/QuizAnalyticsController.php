<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\QuizScore;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class QuizAnalyticsController extends Controller
{
    /**
     * Get classroom list for filter
     * Endpoint: /api/classrooms
     */
    public function getClassrooms()
    {
        $user = Auth::user();
        $classrooms = Classroom::where('instructor_id', $user->id)
            ->select('id', 'class_name', 'subject', 'section_code')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $classrooms,
        ]);
    }

    /**
     * Get quiz performance data with classroom filter
     * Endpoint: /api/quiz-chart
     */
    public function index(Request $request)
    {
        $classroomId = $request->get('classroom_id');

        // Get all unique quiz codes with their titles and due dates
        $quizCodesQuery = QuizQuestion::select('quiz_code', 'quiz_title', 'due_date')
            ->distinct();

        // If classroom filter is applied, get quizzes for that classroom
        if ($classroomId) {
            $quizCodesQuery->where('classroom_id', $classroomId);
        }

        $quizCodes = $quizCodesQuery->get();

        // Get all quiz scores
        $scoresQuery = QuizScore::with('student:id,name');
        if ($classroomId) {
            $scoresQuery->where('classroom_id', $classroomId);
        }
        $scores = $scoresQuery->get();

        // Combine quiz info + aggregated performance
        $chartData = $quizCodes->map(function ($quiz) use ($scores) {
            // Filter scores for this specific quiz_code
            $quizScores = $scores->where('quiz_code', $quiz->quiz_code);

            // Convert scores to numeric values for calculations
            $numericScores = $quizScores->map(function ($score) {
                return floatval($score->score);
            })->filter();

            $averageScore = $numericScores->avg() ?? 0;
            $highestScore = $numericScores->max() ?? 0;
            $lowestScore = $numericScores->min() ?? 0;
            $participants = $numericScores->count();

            return [
                'quiz_title' => $quiz->quiz_title,
                'quiz_code' => $quiz->quiz_code,
                'due_date' => $quiz->due_date,
                'average' => round($averageScore, 2),
                'highest' => round($highestScore, 2),
                'lowest' => round($lowestScore, 2),
                'participants' => $participants,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total_quizzes' => $quizCodes->count(),
            'data' => $chartData,
        ]);
    }

    /**
     * Get student performance distribution with classroom filter
     * Endpoint: /api/student-performance
     */
    public function studentPerformance(Request $request)
    {
        $classroomId = $request->get('classroom_id');

        // Get score ranges distribution across all quiz attempts
        $scoreRanges = [
            '90-100' => 0,
            '80-89' => 0,
            '70-79' => 0,
            '60-69' => 0,
            'Below 60' => 0,
        ];

        // Get all scores and convert to numeric
        $scoresQuery = QuizScore::query();
        if ($classroomId) {
            $scoresQuery->where('classroom_id', $classroomId);
        }
        $allScores = $scoresQuery->get()->map(function ($score) {
            return floatval($score->score);
        });

        foreach ($allScores as $score) {
            if ($score >= 90) {
                $scoreRanges['90-100']++;
            } elseif ($score >= 80) {
                $scoreRanges['80-89']++;
            } elseif ($score >= 70) {
                $scoreRanges['70-79']++;
            } elseif ($score >= 60) {
                $scoreRanges['60-69']++;
            } else {
                $scoreRanges['Below 60']++;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $scoreRanges,
        ]);
    }

    /**
     * Get quiz completion trends over time with classroom filter
     * Endpoint: /api/completion-trends
     */
    public function completionTrends(Request $request)
    {
        $classroomId = $request->get('classroom_id');

        // Generate dates for the last 30 days
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get actual submissions grouped by date
        $submissionsQuery = QuizScore::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as submissions')
        )
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($classroomId) {
            $submissionsQuery->where('classroom_id', $classroomId);
        }

        $actualSubmissions = $submissionsQuery->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Create complete date range with all days
        $trends = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');

            $trends[] = [
                'date' => $dateString,
                'submissions' => $actualSubmissions->has($dateString)
                    ? $actualSubmissions[$dateString]->submissions
                    : 0,
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'status' => 'success',
            'data' => $trends,
        ]);
    }

    /**
     * Get difficulty analysis with classroom filter
     * Endpoint: /api/difficulty-analysis
     */
    public function difficultyAnalysis(Request $request)
    {
        $classroomId = $request->get('classroom_id');

        // Get unique difficulty levels and their quiz counts
        $difficultiesQuery = QuizQuestion::select('difficulty_level', DB::raw('COUNT(DISTINCT quiz_code) as quiz_count'))
            ->groupBy('difficulty_level');

        if ($classroomId) {
            $difficultiesQuery->where('classroom_id', $classroomId);
        }

        $difficulties = $difficultiesQuery->get();

        $difficultyData = [];

        foreach ($difficulties as $difficulty) {
            $level = $difficulty->difficulty_level ?? 'Not Set';

            // Get all quiz_codes with this difficulty level
            $quizCodesQuery = QuizQuestion::where('difficulty_level', $difficulty->difficulty_level);

            if ($classroomId) {
                $quizCodesQuery->where('classroom_id', $classroomId);
            }

            $quizCodes = $quizCodesQuery->pluck('quiz_code')->unique();

            if ($quizCodes->count() > 0) {
                // Get all scores for these quiz codes and convert to numeric
                $scoresQuery = QuizScore::whereIn('quiz_code', $quizCodes);

                if ($classroomId) {
                    $scoresQuery->where('classroom_id', $classroomId);
                }

                $scores = $scoresQuery->get();

                $numericScores = $scores->map(function ($score) {
                    return floatval($score->score);
                })->filter();

                $avgScore = $numericScores->avg() ?? 0;
                $totalAttempts = $numericScores->count();
            } else {
                $avgScore = 0;
                $totalAttempts = 0;
            }

            $difficultyData[] = [
                'level' => $level,
                'quiz_count' => $difficulty->quiz_count,
                'average_score' => round($avgScore, 2),
                'total_attempts' => $totalAttempts,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $difficultyData,
        ]);
    }
}
