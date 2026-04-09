<?php

namespace App\Http;

use App\Http\Middleware\Client\CanClientCreate;
use App\Http\Middleware\Client\CanClientDelete;
use App\Http\Middleware\Client\CanClientUpdate;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\Lead\CanLeadCreate;
use App\Http\Middleware\Lead\CanLeadUpdateStatus;
use App\Http\Middleware\Lead\IsLeadAssigned;
use App\Http\Middleware\LogLastUserActivity;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfDemo;
use App\Http\Middleware\RedirectIfFileSystemIsNotEnabled;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RedirectIfNotSuperAdmin;
use App\Http\Middleware\Task\CanTaskCreate;
use App\Http\Middleware\Task\CanTaskUpdateStatus;
use App\Http\Middleware\Task\IsTaskAssigned;
use App\Http\Middleware\Translation;
use App\Http\Middleware\User\CanUserCreate;
use App\Http\Middleware\User\CanUserUpdate;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            LogLastUserActivity::class,
            SubstituteBindings::class,
            Translation::class,
        ],
        'client.create' => [CanClientCreate::class],
        'client.update' => [CanClientUpdate::class],
        'client.delete' => [CanClientDelete::class],
        'user.create' => [CanUserCreate::class],
        'user.update' => [CanUserUpdate::class],
        'task.create' => [CanTaskCreate::class],
        'task.update.status' => [CanTaskUpdateStatus::class],
        'task.assigned' => [IsTaskAssigned::class],
        'lead.create' => [CanLeadCreate::class],
        'lead.assigned' => [IsLeadAssigned::class],
        'lead.update.status' => [CanLeadUpdateStatus::class],
        'user.is.admin' => [RedirectIfNotAdmin::class],
        'user.is.superadmin' => [RedirectIfNotSuperAdmin::class],
        'filesystem.is.enabled' => [RedirectIfFileSystemIsNotEnabled::class],
        'is.demo' => [RedirectIfDemo::class],
        'api' => [
            'auth:api',
            'throttle:60,1',
            'bindings',

        ],

    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'role' => \App\Zizaco\Entrust\Middleware\EntrustRole::class,
        'permission' => \App\Zizaco\Entrust\Middleware\EntrustPermission::class,
        'ability' => \App\Zizaco\Entrust\Middleware\EntrustAbility::class,
    ];
}
