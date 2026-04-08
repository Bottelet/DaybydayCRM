<?php

namespace App\Http\Middleware\Client;

use Closure;
use Illuminate\Http\Request;

class CanClientCreate
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! auth()->user()->can('client-create')) {
            Session()->flash('flash_message_warning', __("You don't have permission to create a client"));

            return redirect()->route('clients.index');
        }

        return $next($request);
    }
}
