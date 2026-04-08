<?php

namespace App\Http\Middleware\Lead;

use Closure;
use Illuminate\Http\Request;

class CanLeadCreate
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->can('lead-create')) {
            Session()->flash('flash_message_warning', __("You don't have permission to create a lead"));

            return redirect()->route('leads.index');
        }

        return $next($request);
    }
}
