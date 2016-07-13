<?php

namespace App\Policies;

use App\Tasks;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Auth;
Use Settings;

class allowTaskComplete
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
       
    }
    public function notAllcomplete(Settings $settings)
    {
        return $settings->task_complete_allowed == 1;
    }

    public function allcomplete(Settings $settings)
    {
        return $settings->task_complete_allowed == 2;
    }

    public function AllowUserComplete(User $user, Tasks $task)
    {
        return Auth::user()->id == $task->fk_user_id_assign;
    }
}
