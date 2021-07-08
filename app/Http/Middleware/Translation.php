<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

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
            if (!in_array($language, ["en", "dk", "es", "fa"])) {
                $language = "en";
            }
            app()->setLocale($language);
            if ($language === "fa") {
                Config::set('app.direction', 'rtl');
            }
        }
        return $next($request);
    }
}
