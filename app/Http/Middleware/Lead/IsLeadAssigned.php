<?php

namespace App\Http\Middleware\Lead;

use Closure;
use App\Models\Setting;
use App\Models\Lead;

class IsLeadAssigned
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
        if (!auth()->user()->can('can-assign-new-user-to-lead')) {
            Session()->flash('flash_message_warning', __("You don't have the right permission for this action"));
            return redirect()->back();
        }

        return $next($request);
    }
}
