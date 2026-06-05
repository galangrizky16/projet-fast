<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        return $request->wantsJson() && !$request->hasHeader('X-Inertia')
            ? response()->json('', 204)
            : redirect()->route('login');
    }
}
