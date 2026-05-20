<?php

namespace App\Http\Middleware\Lead;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class CanLeadCreate
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
        $message = __("You don't have permission to create a lead");

        if ( ! $user?->can(PermissionName::LEAD_CREATE->value)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            session()->flash('flash_message_warning', $message);

            return redirect()->route('leads.index');
        }

        return $next($request);
    }
}
