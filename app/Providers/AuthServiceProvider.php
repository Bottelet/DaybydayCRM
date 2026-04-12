<?php

namespace App\Providers;

use App\Models\Task;
use App\Policies\AllowTaskComplete;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        Task::class => AllowTaskComplete::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        // Integrate Entrust role-based permissions with Laravel's Gate so that
        // $user->can('permission-name') correctly checks Entrust permissions.
        $gate->before(function ($user, $ability) {
            if (! method_exists($user, 'cachedRoles')) {
                return null;
            }

            foreach ($user->cachedRoles() as $role) {
                if (! is_object($role) || ! method_exists($role, 'cachedPermissions')) {
                    continue;
                }

                foreach ($role->cachedPermissions() as $perm) {
                    if (! is_object($perm) || empty($perm->name)) {
                        continue;
                    }

                    if (Str::is($perm->name, $ability)) {
                        return true;
                    }
                }
            }

            return null;
        });
    }
}
