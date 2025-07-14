<?php

use App\Http\Middleware\InstructorMiddleware;
use App\Http\Middleware\StudentMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->alias([
            'instructor' => InstructorMiddleware::class,
            'students' => StudentMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
