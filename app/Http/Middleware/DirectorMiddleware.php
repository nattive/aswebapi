<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DirectorMiddleware
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
        if (auth("sanctum")->user()->role == 'DIRECTOR') {
            return $next($request);
        }
        return response()->json([
            'message' => "You are not authorized to perform this function",
            'success' => false,
        ], 401);

    }
}
