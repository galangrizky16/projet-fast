<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $allowedRoles): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $user->loadMissing('role');

        $roleSlug = Str::slug((string) ($user->role?->slug ?? ''));
        $allowed = array_map(
            static fn(string $role): string => Str::slug($role),
            explode(',', $allowedRoles),
        );

        abort_unless(in_array($roleSlug, $allowed, true), 403);

        return $next($request);
    }
}
