<?php

namespace App\Http\Middleware\Client;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class CanClientDelete
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
        $message = __("You don't have permission to delete a client");

        if ( ! $user?->can(PermissionName::CLIENT_DELETE->value)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            session()->flash('flash_message_warning', $message);

            return redirect()->route('clients.index');
        }

        return $next($request);
    }
}
