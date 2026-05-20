<?php

namespace App\Services\User;

use App\Enums\RoleType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UserUpdateService
{
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
