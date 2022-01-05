<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TopLevelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth("sanctum")->user();
        if ($user->role == 'SUPERVISOR'  || $user->role == 'DIRECTOR') {
            return $next($request);
        }
        return response()->json([
            'errors' => ["You are not authorized to perform this function"],
            'success' => false,
        ], 401);
    }
}
