<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('redirect.dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Override login Fortify → pakai Login.vue
Route::get('/login', function () {
    return Inertia::render('auth/Login', [
        'canResetPassword' => true,
        'canRegister'      => true,
        'status'           => session('status'),
        'roles'            => \App\Models\Role::orderBy('nama')->get(['slug', 'nama']),
    ]);
})->middleware('guest')->name('login');

// Override register Fortify → pakai Register.vue 
Route::get('/register', function () {
    return Inertia::render('auth/Register', [
        'roles'         => \App\Models\Role::whereNotIn('slug', ['super-admin'])
            ->orderBy('nama')->get(['id', 'nama', 'slug']),
        'programStudis' => \App\Models\ProgramStudi::orderBy('nama')->get(['id', 'nama']),
    ]);
})->middleware('guest')->name('register');

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'store'])
    ->middleware('guest')
    ->name('register.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('redirect.dashboard');
    })->name('dashboard');

    Route::get('redirect-dashboard', function (Request $request) {
        $user = $request->user();
        abort_if($user === null, 403);

        $user->loadMissing('role');

        $roleSlug = str((string) ($user->role?->slug ?? ''))->slug()->toString();
        $roleName = str((string) ($user->role?->nama ?? ''))->slug()->toString();

        Log::info('Redirect dashboard - computed role', [
            'user_id' => $user->id,
            'role_slug' => $roleSlug,
            'role_name' => $roleName,
        ]);

        // Prefer model helper methods for consistent role checks
        if ($user->hasRole('admin')) {
            Log::info('Redirecting to admin.dashboard', ['user_id' => $user->id, 'role' => $user->roleSlug()]);
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('kaprodi')) {
            Log::info('Redirecting to kaprodi.dashboard', ['user_id' => $user->id, 'role' => $user->roleSlug()]);
            return redirect()->route('kaprodi.dashboard');
        }

        if ($user->hasRole('dekan')) {
            Log::info('Redirecting to dekan.dashboard', ['user_id' => $user->id, 'role' => $user->roleSlug()]);
            return redirect()->route('dekan.dashboard');
        }

        if ($user->hasFastUserRole()) {
            Log::info('Redirecting to fast.user.dashboard', ['user_id' => $user->id, 'role' => $user->roleSlug()]);
            return redirect()->route('fast.user.dashboard');
        }

        Log::warning('No matching role for redirect', ['user_id' => $user->id, 'role_slug' => $roleSlug, 'role_name' => $roleName]);
        abort(403);
    })->name('redirect.dashboard');
});

require __DIR__ . '/fast.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/qr_verification.php';
