<?php

namespace App\Http\Middleware;

use Cache;
use Carbon;
use Closure;

class LogLastUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check()) {
            $expiresAt = Carbon::now()->addMinutes(5);
            Cache::put('user-is-online-'.\Auth::user()->id, true, $expiresAt);
        }

        return $next($request);
    }
}
