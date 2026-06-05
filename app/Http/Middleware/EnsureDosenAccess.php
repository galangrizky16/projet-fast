<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureDosenAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $user->loadMissing('role');

        $roleSlug = Str::slug((string) ($user->role?->slug ?? ''));
        $roleName = Str::slug((string) ($user->role?->nama ?? ''));

        $allowedRoles = ['dosen', 'lecturer', 'dosen-tetap', 'dosen-luar-biasa'];

        $hasAccess = in_array($roleSlug, $allowedRoles, true)
            || in_array($roleName, $allowedRoles, true)
            || Str::contains($roleSlug, 'dosen')
            || Str::contains($roleName, 'dosen');

        abort_unless($hasAccess, 403);

        return $next($request);
    }
}
