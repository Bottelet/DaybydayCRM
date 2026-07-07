<?php

namespace App\Services\User;

use App\Enums\RoleType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class UserUpdateService
{
    /**
     * Create a new user with the given validated attributes, role, and
     * department. Matches the previous inline UsersController@store logic
     * exactly - including the language fallback to 'en' for anything
     * outside the 3 supported locales.
     */
    public function create(array $validated, ?UploadedFile $imageFile): User
    {
        $settings = Setting::cached();

        $path = null;
        if ($imageFile !== null) {
            $path = Storage::put($settings->external_id, $imageFile);
        }

        return DB::transaction(function () use ($validated, $path): User {
            $user                   = new User();
            $user->name             = $validated['name'];
            $user->external_id      = Uuid::uuid4()->toString();
            $user->email            = $validated['email'];
            $user->address          = $validated['address'] ?? null;
            $user->primary_number   = $validated['primary_number'] ?? null;
            $user->secondary_number = $validated['secondary_number'] ?? null;
            $user->password         = bcrypt($validated['password']);
            $user->image_path       = $path;
            $user->language         = in_array($validated['language'] ?? null, ['en', 'dk', 'es'], true) ? $validated['language'] : 'en';
            $user->save();
            $user->roles()->attach($validated['role']);
            $user->department()->attach($validated['department']);
            $user->save();

            return $user;
        });
    }

    public function prepareValidatedInput(User $authenticatedUser, User $user, array $input, ?UploadedFile $imageFile): array
    {
        if ( ! $authenticatedUser->canChangePasswordOn($user)) {
            unset($input['password'], $input['password_confirmation']);
        }

        if (isset($input['password']) && $input['password'] !== '') {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password'], $input['password_confirmation']);
        }

        if ($imageFile !== null) {
            $setting = Setting::query()->first();
            if ( ! $setting) {
                throw new RuntimeException('No company settings found. Please configure company settings or contact support if this persists.');
            }

            $companyExternalId   = $setting->external_id;
            $input['image_path'] = Storage::put($companyExternalId, $imageFile);
        }

        return $input;
    }

    public function syncRoleAndDepartment(User $authenticatedUser, User $user, int $roleId, int $departmentId): bool
    {
        if ($authenticatedUser->canChangeRole()) {
            $owners = User::whereHas('roles', function ($query) {
                $query->where('name', RoleType::OWNER->value);
            })->count();

            $currentRole = $user->roles->first();
            if ($currentRole && $currentRole->name === RoleType::OWNER->value && $owners <= 1) {
                return false;
            }

            $user->roles()->sync([$roleId]);
        }

        $user->department()->sync([$departmentId]);

        return true;
    }
}
