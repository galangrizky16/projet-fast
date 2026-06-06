<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureApprovalAccess;
use App\Http\Middleware\EnsureDosenAccess;
use App\Http\Middleware\EnsureFastUserAccess;
use App\Http\Middleware\EnsureRoleAccess;
use App\Http\Middleware\EnsureStaffAccess;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'admin.access'   => EnsureAdminAccess::class,
            'approval.access' => EnsureApprovalAccess::class,
            'dosen.access'   => EnsureDosenAccess::class,
            'fast.user'      => EnsureFastUserAccess::class,
            'role'           => EnsureRoleAccess::class,
            'staff.access'   => EnsureStaffAccess::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
