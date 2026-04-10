<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth()->user();
        if ($user && ($user->hasRole('administrator') || $user->hasRole('owner'))) {
            return $next($request);
        }
        Session()->flash('flash_message_warning', __('Only Allowed for admins'));

        return redirect()->back();
    }
}
