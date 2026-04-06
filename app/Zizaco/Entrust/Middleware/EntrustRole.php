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

class EntrustRole
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
    public function handle($request, Closure $next, $roles)
    {
        if (! is_array($roles)) {
            $roles = explode(self::DELIMITER, $roles);
        }

        if ($this->auth->guest() || ! $request->user()->hasRole($roles)) {
            abort(403);
        }

        return $next($request);
    }
}
