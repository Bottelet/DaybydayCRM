<?php

namespace App\Http\Middleware\Task;

use Closure;
use Illuminate\Http\Request;

class CanTaskCreate
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->can('task-create')) {
            Session()->flash('flash_message_warning', __("You don't have permission to create a task"));

            return redirect()->route('tasks.index');
        }

        return $next($request);
    }
}
