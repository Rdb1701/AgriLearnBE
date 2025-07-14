<?php

use App\Mail\SendStudentsEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/send-email', function () {
//     Mail::to('ronaldbesinga287@gmail.com')->send(new SendStudentsEmail());
//     return 'Email has been sent!';
// });
