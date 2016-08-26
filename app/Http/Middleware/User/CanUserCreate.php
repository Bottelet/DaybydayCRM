<?php

namespace App\Http\Middleware\User;

use Closure;

class CanUserCreate
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
        if (!auth()->user()->can('user-create')) {
            Session()->flash('flash_message_warning', 'Not allowed to create user');
            return redirect()->route('users.index');
        }
        return $next($request);
    }
}
