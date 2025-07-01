<?php

use App\Console\Commands\Test;
use App\Models\SocialAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $socialAccount = SocialAccount::first();
        $schedule->command('xxx', [$socialAccount])
            ->everyMinute()
            ->appendOutputTo(storage_path('/schedule.log'))
            ->onSuccess(function () {
                $this->info('Scheduled task completed successfully!');
            })
            ->onFailure(function () {
                $this->info('Scheduled task completed successfully!');
            });
        // $schedule->exec('rundll32.exe user32.dll,LockWorkStation')
        //     ->everyMinute()
        //     ->appendOutputTo(storage_path('/windows-lock.log'))
        //     ->onSuccess(function () {
        //         $this->info('Windows locked successfully!');
        //     })
        //     ->onFailure(function () {
        //         $this->error('Failed to lock Windows!');
        //     });
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('posts:process-scheduled')->everyMinute();
    })
    ->create();
