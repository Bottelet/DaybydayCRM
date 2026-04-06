<?php

namespace App\Http\Middleware\Task;

use Closure;

class CanTaskUpdateStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->can('task-update-status')) {
            Session()->flash('flash_message_warning', __("You don't have the right permission for this action"));

            return redirect()->back();
        }

        return $next($request);
    }
}
