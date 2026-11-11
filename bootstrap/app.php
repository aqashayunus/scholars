<?php

use App\Http\Middleware\EnsurePasswordIsNotExpired;
use App\Http\Middleware\EnsureStudentAccessNotRestricted;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TeacherActiveMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'logout',
            'billing/billplz/webhook',
            'billing/gateway/webhook',
        ]);
        $middleware->appendToGroup('web', EnsureUserIsActive::class);
        $middleware->appendToGroup('web', EnsurePasswordIsNotExpired::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'teacher.active' => TeacherActiveMiddleware::class,
            'student.not_restricted' => EnsureStudentAccessNotRestricted::class,
            'user.active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
