<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassEnrollmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\InstructionalMaterialController;
use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\QuizController;
use App\Mail\SendStudentsEmail;
use App\Models\ClassEnrollment;
use App\Models\InstructionalMaterial;
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

    //Classroom Routes
    Route::apiResource('/classroom', ClassroomController::class);

    //class enrollment
    Route::get('/class_enrollment', [ClassEnrollmentController::class, 'index']);
    Route::post('/class_enrollment', [ClassEnrollmentController::class, 'store']);
    Route::delete('/class_enrollment/{class_enrollment}', [ClassEnrollmentController::class, 'destroy']);

    //MATERIALS
    Route::apiResource('/materials', InstructionalMaterialController::class);
    Route::get('/classroom/{id}/materials', [InstructionalMaterialController::class, 'getMaterialByClassroom']);

    //QUIZZES
    Route::apiResource('/quizzes', QuizController::class);
    Route::get('/quiz/{id}/quizzes', [QuizController::class, 'getQuizQuestionsDistinct']);
    Route::get('/quiz/{classroom_id}/quizzes/{created_at}', [QuizController::class, 'getQuizQuestions']);
    Route::delete('quiz/{id}/deleteAll/{created_at}', [QuizController::class, 'destroyAll']);
});


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'signup']);
