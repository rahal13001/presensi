<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckUserGroup;
use App\Http\Middleware\CheckApiDocAccess;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use App\Console\Commands\AssignCommonEmployeeShifts;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'api-doc-access' => CheckApiDocAccess::class,
            'check-user-group' => CheckUserGroup::class
        ]);
        
    })
    ->withCommands([
        AssignCommonEmployeeShifts::class
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
