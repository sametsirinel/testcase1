<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class UserRateLimiterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key = "default"): Response
    {
        if (RateLimiter::tooManyAttempts('user:'.$key.':'.$request->user()->id,10)) {
            \abort(Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::increment('user:'.$key.':'.$request->user()->id);

        return $next($request);
    }
}
