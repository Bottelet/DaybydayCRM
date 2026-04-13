<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Translation
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()) {
            $language = auth()->user()->language;
            if ( ! in_array($language, ['en', 'dk', 'es'])) {
                $language = 'en';
            }
            app()->setLocale($language);
        }

        return $next($request);
    }
}
