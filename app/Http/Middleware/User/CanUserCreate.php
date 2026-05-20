<?php

namespace App\Http\Middleware\User;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class CanUserCreate
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
        $message = __("You don't have permission to create a user");

        if ( ! $user?->can(PermissionName::USER_CREATE->value)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            session()->flash('flash_message_warning', $message);

            return redirect()->route('users.index');
        }

        return $next($request);
    }
}
