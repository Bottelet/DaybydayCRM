<?php

namespace App\Http\Middleware\User;

use Closure;

class CanUserUpdate
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
        if (!auth()->user()->can('user-update')) {
            session()->flash('flash_message_warning', __("You don't have permission to update a client"));
            return redirect()->route('users.index');
        }
        return $next($request);
    }
}
