<?php

namespace App\Http\Middleware\Client;

use Closure;
use Illuminate\Http\Request;
use Log;

class CanClientCreate
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
        $user = auth()->user();

        if (config('app.debug')) {
            Log::debug('CanClientCreate middleware check', ['user_id' => $user?->id]);
        }

        if ( ! $user->can('client-create')) {
            session()->flash('flash_message_warning', __("You don't have permission to create a client"));

            return redirect()->route('clients.index');
        }

        return $next($request);
    }
}
