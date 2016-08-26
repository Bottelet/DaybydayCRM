<?php

namespace App\Http\Middleware\Client;

use Closure;

class CanClientCreate
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
        if (!auth()->user()->can('client-create')) {
            Session()->flash('flash_message_warning', 'Not allowed to create client!');
            return redirect()->route('clients.index');
        }

        return $next($request);
    }
}
