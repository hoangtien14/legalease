<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ActiveUserMiddleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superadmin' => SuperAdminMiddleware::class,
            'role' => RoleMiddleware::class,
            'active' => ActiveUserMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('appointments:remind')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Default exception configuration.
    })->create();
