<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
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

        $hasAccess = in_array($roleSlug, ['admin'], true)
            || in_array($roleName, ['admin'], true);

        abort_unless($hasAccess, 403);

        return $next($request);
    }
}
