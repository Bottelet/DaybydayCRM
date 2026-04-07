<?php

namespace App\Http\Middleware\Client;

use Closure;
use Illuminate\Http\Request;

class CanClientUpdate
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->can('client-update')) {
            Session()->flash('flash_message_warning', __("You don't have permission to update a user"));

            return redirect()->route('clients.index');
        }

        return $next($request);
    }
}
