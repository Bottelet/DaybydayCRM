<?php

namespace App\Http\Middleware\Task;

use Closure;
use App\Settings;
use App\Tasks;

class IsTaskAssigned
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
        $task = Tasks::findOrFail($request->id);
        $settings = Settings::all();
        $isAdmin = Auth()->user()->hasRole('administrator');
        $settingscomplete = $settings[0]['task_assign_allowed'];
    
        if ($settingscomplete == 1  && Auth()->user()->id != $task->fk_user_id_assign || $isAdmin) {
            Session()->flash('flash_message_warning', 'Only assigned user are allowed to do this');
                return redirect()->back();
        }
        return $next($request);
    }
}
