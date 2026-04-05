<?php

namespace App\Http\Middleware\Task;

use Closure;

class IsTaskAssigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->can('can-assign-new-user-to-task')) {
            Session()->flash('flash_message_warning', __("You don't have the right permission for this action"));

            return redirect()->back();
        }

        return $next($request);
    }
}
