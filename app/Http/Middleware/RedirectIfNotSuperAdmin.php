<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfNotSuperAdmin
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
        if (Auth()->user()->hasRole('owner')) {
            return $next($request);
        }
        Session()->flash('flash_message_warning', __('Only Allowed for the user who registered the CRM'));
        return redirect()->back();
    }
}
