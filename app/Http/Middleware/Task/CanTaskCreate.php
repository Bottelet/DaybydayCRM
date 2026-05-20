<?php

namespace App\Http\Middleware\Task;

use App\Enums\PermissionName;
use Closure;
use Illuminate\Http\Request;

class CanTaskCreate
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
        $message = __("You don't have permission to create a task");

        if ( ! $user?->can(PermissionName::TASK_CREATE->value)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            session()->flash('flash_message_warning', $message);

            return redirect()->route('tasks.index');
        }

        return $next($request);
    }
}
