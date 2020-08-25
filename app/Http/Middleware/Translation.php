<?php

namespace App\Http\Middleware;

use Closure;

class Translation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()) {
            $language = auth()->user()->language;
            if (!in_array($language, ["en", "dk", "es"])) {
                $language = "en";
            }
            app()->setLocale($language);
        }
        return $next($request);
    }
}
