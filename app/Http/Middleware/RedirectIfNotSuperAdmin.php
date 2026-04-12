<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfNotSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth()->user()->hasRole('owner')) {
            return $next($request);
        }
        session()->flash('flash_message_warning', __('Only Allowed for the user who registered the CRM'));

        return redirect()->back();
    }
}
