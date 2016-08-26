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
        if (!auth()->user()->hasRole('administrator')) {
            Session()->flash('flash_message_warning', 'Only Allowed for admins');
            return redirect()->back();
        }
        return $next($request);
    }
}
