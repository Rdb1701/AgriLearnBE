<?php

use App\Http\Controllers\Api\OAuthController;
use App\Mail\SendStudentsEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/send-email', function () {
//     Mail::to('ronaldbesinga287@gmail.com')->send(new SendStudentsEmail());
//     return 'Email has been sent!';
// });


// Google OAuth redirect
Route::get('/auth/google/redirect',[OAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [OAuthController::class, 'callback']);

Route::get('/auth/google/instructor/redirect', [OAuthController::class, 'instructorRedirect'])->name('google.instructor.redirect');
Route::get('/auth/google/instructor/callback', [OAuthController::class, 'instructorCallback'])->name('google.instructor.callback');