<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    //membuat custom error untuk API yang TIDAK menggunakan token atau token expired
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'meta' => [
                'code' => 401, // Internal Server Error status code
                'status' => false,
                'message' => 'Unauthorized',
            ],
        ], 401));
    }
}
