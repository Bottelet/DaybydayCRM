<?php

namespace App\Http\Middleware\Lead;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class IsLeadAssigned
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
        $message = __("You don't have the right permission for this action");

        if ( ! $user?->can(PermissionName::LEAD_ASSIGN->value)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            session()->flash('flash_message_warning', $message);

            return redirect()->back();
        }

        return $next($request);
    }
}
