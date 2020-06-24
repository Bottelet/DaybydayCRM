<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfNotAdmin
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
        if (Auth()->user()->hasRole('administrator') || Auth()->user()->hasRole('owner')) {
            return $next($request);
        }
        Session()->flash('flash_message_warning', __('Only Allowed for admins'));
        return redirect()->back();
    }
}
