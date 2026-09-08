<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status === 'inactive') {
            if (!$request->routeIs('banned') && !$request->routeIs('logout')) {
                return redirect()->route('banned');
            }
        }

        return $next($request);
    }
}
