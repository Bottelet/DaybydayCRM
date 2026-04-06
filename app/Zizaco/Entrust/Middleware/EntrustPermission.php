<?php

namespace App\Zizaco\Entrust\Middleware;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class EntrustPermission
{
    const DELIMITER = '|';

    protected $auth;

    /**
     * Creates a new instance of the middleware.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        if (! is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        if ($this->auth->guest() || ! $request->user()->can($permissions)) {
            abort(403);
        }

        return $next($request);
    }
}
