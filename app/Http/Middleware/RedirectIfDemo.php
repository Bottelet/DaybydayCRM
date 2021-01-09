<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfDemo
{
    const MEESAGE = 'This action is not allowed in the demo.';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!isDemo()) {
            return $next($request);
        }
        
        Session()->flash('flash_message_warning', __(self::MEESAGE));
        return redirect()->back();
    }
}
