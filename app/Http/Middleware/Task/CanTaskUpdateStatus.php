<?php

namespace App\Http\Middleware\Task;

use Closure;
use App\Models\Setting;
use App\Models\Task;

class CanTaskUpdateStatus
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
        if (!auth()->user()->can('task-update-status')) {
            Session()->flash('flash_message_warning', __("You don't have the right permission for this action"));
            return redirect()->back();
        }

        return $next($request);
    }
}
