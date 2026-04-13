<?php

namespace App\Http\Middleware\Lead;

use Closure;
use Illuminate\Http\Request;

class CanLeadUpdateStatus
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! auth()->user()->can('lead-update-status')) {
            session()->flash('flash_message_warning', __("You don't have the right permission for this action"));

            return redirect()->back();
        }

        return $next($request);
    }
}
