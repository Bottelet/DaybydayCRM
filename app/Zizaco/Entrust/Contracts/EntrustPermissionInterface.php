<?php

namespace App\Zizaco\Entrust\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 */
interface EntrustPermissionInterface
{
    /**
     * Many-to-Many relations with role model.
     *
     * @return BelongsToMany
     */
    public function roles();
}
