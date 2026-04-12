<?php

namespace App\Zizaco\Entrust\Traits;

use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait EntrustUserTrait
{
    public function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey = 'entrust_roles_for_user_'.$this->$userPrimaryKey;
        $roleModel = Config::get('entrust.role');

        if (Cache::getStore() instanceof TaggableStore) {
            $rolesArray = Cache::tags(
                Config::get('entrust.role_user_table')
            )->remember(
                $cacheKey,
                Config::get('cache.ttl'),
                function () {
                    return $this->roles()->get()->toArray();
                }
            );

            $roles = collect($rolesArray)->map(
                function ($roleArr) use ($roleModel) {
                    return (new $roleModel())
                        ->newFromBuilder($roleArr);
                }
            );
        } else {
            $roles = $this->roles()->get();
        }

        return $roles->filter(function ($role) {
            if (! is_object($role)) {
                Log::warning(
                    'EntrustUserTrait: Non-object found in cachedRoles for user ID '.
                    $this->getKey()
                );

                return false;
            }

            return true;
        });
    }

    public function save(array $options = [])
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(
                Config::get('entrust.role_user_table')
            )->flush();
        }

        return parent::save($options);
    }

    public function delete(array $options = [])
    {
        $result = parent::delete($options);

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(
                Config::get('entrust.role_user_table')
            )->flush();
        }

        return $result;
    }

    public function restore()
    {
        $result = parent::restore();

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(
                Config::get('entrust.role_user_table')
            )->flush();
        }

        return $result;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('entrust.role'),
            Config::get('entrust.role_user_table'),
            Config::get('entrust.user_foreign_key'),
            Config::get('entrust.role_foreign_key')
        );
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if (! method_exists(
                Config::get('auth.providers.users.model'),
                'bootSoftDeletes'
            )) {
                $user->roles()->sync([]);
            }

            return true;
        });
    }

    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && ! $requireAll) {
                    return true;
                }

                if (! $hasRole && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        }

        foreach ($this->cachedRoles() as $role) {
            if (! is_object($role)) {
                continue;
            }

            if ($role->name == $name) {
                return true;
            }
        }

        return false;
    }

    public function can($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($permName);

                if ($hasPerm && ! $requireAll) {
                    return true;
                }

                if (! $hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        }

        foreach ($this->cachedRoles() as $role) {
            if (! is_object($role)
                || ! method_exists($role, 'cachedPermissions')
            ) {
                continue;
            }

            foreach ($role->cachedPermissions() as $perm) {
                if (! is_object($perm)
                    || empty($perm->name)
                ) {
                    continue;
                }

                if (Str::is($permission, $perm->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function ability($roles, $permissions, $options = [])
    {
        if (! is_array($roles)) {
            $roles = explode(',', $roles);
        }

        if (! is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        if (! isset($options['validate_all'])) {
            $options['validate_all'] = false;
        }

        if (! isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        }

        $checkedRoles = [];
        $checkedPermissions = [];

        foreach ($roles as $role) {
            $checkedRoles[$role] =
                $this->hasRole($role);
        }

        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] =
                $this->can($permission);
        }

        if (
            ($options['validate_all']
                && ! in_array(false, $checkedRoles)
                && ! in_array(false, $checkedPermissions))
            ||
            (! $options['validate_all']
                && (in_array(true, $checkedRoles)
                || in_array(true, $checkedPermissions)))
        ) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        if ($options['return_type'] === 'boolean') {
            return $validateAll;
        }

        if ($options['return_type'] === 'array') {
            return [
                'roles' => $checkedRoles,
                'permissions' => $checkedPermissions,
            ];
        }

        return [
            $validateAll,
            [
                'roles' => $checkedRoles,
                'permissions' => $checkedPermissions,
            ],
        ];
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     * Uses syncWithoutDetaching to prevent duplicate key errors.
     *
     * @param  mixed  $role
     */
    public function attachRole($role)
    {
        $roleId = $this->resolveRoleId($role);

        $this->roles()->syncWithoutDetaching([
            $roleId,
        ]);

        // Clear the cache after attaching a role
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_user_table'))->flush();
        }
    }

    public function detachRole($role)
    {
        $roleId = $this->resolveRoleId($role);

        $this->roles()->detach($roleId);

        $this->flushRoleCache();
    }

    /**
     * Attach multiple roles to a user
     * Uses syncWithoutDetaching to prevent duplicate key errors.
     *
     * @param  mixed  $roles
     */
    public function attachRoles($roles)
    {
        // Collect role IDs
        $roleIds = collect($roles)->map(function ($role) {
            if (is_object($role)) {
                return $role->getKey();
            }
            if (is_array($role)) {
                return $role['id'] ?? $role;
            }

            return $role;
        })->toArray();

        // Use syncWithoutDetaching to prevent duplicate key errors
        $this->roles()->syncWithoutDetaching($roleIds);

        $this->flushRoleCache();
    }

    public function detachRoles($roles = null)
    {
        if (! $roles) {
            $roles = $this->roles()->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }

    protected function resolveRoleId($role): int
    {
        if (is_object($role)) {
            return (int) $role->getKey();
        }

        if (is_array($role)) {
            return (int) $role['id'];
        }

        return (int) $role;
    }

    protected function flushRoleCache(): void
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(
                Config::get('entrust.role_user_table')
            )->flush();
        }
    }

    public function scopeWithRole($query, $role)
    {
        return $query->whereHas(
            'roles',
            function ($query) use ($role) {
                $query->where('name', $role);
            }
        );
    }
}
