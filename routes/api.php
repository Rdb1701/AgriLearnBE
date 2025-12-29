<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassEnrollmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\InstructionalMaterialController;
use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizScoreController;
use App\Http\Controllers\Api\QuizAnalyticsController;
use App\Http\Controllers\Api\RoomTaskController;
use App\Mail\SendStudentsEmail;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\InstructionalMaterial;
use App\Models\QuizScore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'instructor'])->group(function () {

    //User Routes
    Route::apiResource('/users', UserController::class);
    Route::patch('/users/deactivate/{user}', [UserController::class, 'deactivate'])->name('users.deactivate');

    //class enrollment
    Route::post('/class_enrollment', [ClassEnrollmentController::class, 'store']);
    Route::delete('/class_enrollment/{class_enrollment}', [ClassEnrollmentController::class, 'destroy']);

    //QUIZZES
    Route::apiResource('/quizzes', QuizController::class);

    Route::delete('quiz/{id}/deleteAll/{created_at}', [QuizController::class, 'destroyAll']);

    //get student scores
    Route::get('/quiz-scores', [QuizScoreController::class, 'index']);

    //get scores per classroom
    Route::get('/quiz-scores/classroom/{id}', [QuizScoreController::class, 'getScoresByClassroom']);

    //archive
    Route::put('/archive/classroom/{id}', [ClassroomController::class, 'archiveClass']);

    //archives
    Route::get('/classroom/archive', [ClassroomController::class, 'getArchive']);

    //dashboard Analytisc
    Route::get('/quiz-chart', [QuizAnalyticsController::class, 'index']);
    Route::get('/classrooms', [QuizAnalyticsController::class, 'getClassrooms']);
    Route::get('/student-performance', [QuizAnalyticsController::class, 'studentPerformance']);
    Route::get('/completion-trends', [QuizAnalyticsController::class, 'completionTrends']);
    Route::get('/difficulty-analysis', [QuizAnalyticsController::class, 'difficultyAnalysis']);

    // Room Tasks
    Route::apiResource('/room-tasks', RoomTaskController::class);
});

//STUDENT ROUTES
Route::middleware(['auth:sanctum', 'students'])->group(function () {
    Route::get('/student/classes', [ClassroomController::class, 'getStudentClass']);

    //ENROLL VIA SECTION CODE
    Route::post('/enroll/code', [ClassEnrollmentController::class, 'joinViaCode']);

    //Get Instructor
    Route::get('/getInstructor/{id}', [ClassEnrollmentController::class, 'getInstructor']);

    //Enrollment Status Fase/true
    Route::get('/getEnrollmentStatus', [ClassEnrollmentController::class, 'getEnrollmentStatus']);

    //accept enrollment
    Route::put('/acceptEnrollment/{id}', [ClassEnrollmentController::class, 'acceptEnrollment']);

    //reject enrollment
    Route::delete('/rejectEnrollment/{id}', [ClassEnrollmentController::class, 'rejectEnrollment']);

    Route::post('/classroom/{classroom}/game/save', [GameController::class, 'save']);
    Route::get('/classroom/{classroom}/game/load', [GameController::class, 'load']);
    Route::delete('/classroom/game/delete', [GameController::class, 'delete']);
    Route::get('/classroom/game/saves', [GameController::class, 'getAllSaves']);
    Route::get('classroom/{classroom}/game/has-save', [GameController::class, 'hasSave']);

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    //Classroom Routes
    Route::apiResource('/classroom', ClassroomController::class);
    Route::get('/classroom/{id}/materials', [InstructionalMaterialController::class, 'getMaterialByClassroom']);

    //get people
    Route::get('/class_enrollment', [ClassEnrollmentController::class, 'index']);

    //MATERIALS
    Route::apiResource('/materials', InstructionalMaterialController::class);
    //Quiz
    Route::get('/quiz/{id}/quizzes', [QuizController::class, 'getQuizQuestionsDistinct']);

    //get questions
    Route::get('/quiz/{classroom_id}/quizzes/{created_at}', [QuizController::class, 'getQuizQuestions']);

    //submit quiz
    Route::post('/quiz/{classroomID}/submit', [QuizScoreController::class, 'submitQuiz']);
    //check if already submiotted quiz
    Route::get('/quiz/{classroomID}/{quiz_code}/user', [QuizScoreController::class, 'getUserQuiz']);

    //get status
    Route::get('/classroom/getStatus/{id}', [ClassroomController::class, 'getClassroomStatus']);

    Route::get('/questions', [QuizAnalyticsController::class, 'Allquestions']);
    Route::get('/scores', [QuizAnalyticsController::class, 'Allscores']);

    // Room Tasks General
    Route::get('/tasks', [RoomTaskController::class, 'getAllTasks']);
    Route::get('/room-tasks/user/room/{classroom}', [RoomTaskController::class, 'getUserRoomTasks']);
    Route::put('/room-tasks/user/task/{classroom}', [RoomTaskController::class, 'updateUserRoomTask']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'signup']);
