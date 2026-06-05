<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAccess
{
    /**
     * Allows admin, kaprodi, dekan, and dosen to access
     * shared staff pages (dashboard overview, archive).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $user->loadMissing('role');

        $roleSlug = Str::slug((string) ($user->role?->slug ?? ''));
        $roleName = Str::slug((string) ($user->role?->nama ?? ''));

        $allowedRoles = ['admin', 'kaprodi', 'dekan', 'dosen'];

        $hasAccess = in_array($roleSlug, $allowedRoles, true)
            || in_array($roleName, $allowedRoles, true)
            || Str::contains($roleSlug, 'dosen')
            || Str::contains($roleName, 'dosen');

        \Log::info('EnsureStaffAccess check', [
            'path' => $request->path(),
            'user_id' => $user->id,
            'role_slug' => $roleSlug,
            'role_name' => $roleName,
            'has_access' => $hasAccess,
        ]);

        abort_unless($hasAccess, 403);

        return $next($request);
    }
}
