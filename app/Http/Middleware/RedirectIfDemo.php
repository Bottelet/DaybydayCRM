<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfDemo
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
        if(!isDemo()) {
            return $next($request);
        }
        
        Session()->flash('flash_message_warning', __('This action is not allowed in the demo.'));
        return redirect()->back();
    }
}
