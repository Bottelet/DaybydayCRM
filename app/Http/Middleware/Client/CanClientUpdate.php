<?php

namespace App\Http\Middleware\Client;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class CanClientUpdate
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
        $user    = auth()->user();
        $message = __("You don't have permission to update a client");

        if ( ! $user?->can(PermissionName::CLIENT_UPDATE->value)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            session()->flash('flash_message_warning', $message);

            return redirect()->route('clients.index');
        }

        return $next($request);
    }
}
