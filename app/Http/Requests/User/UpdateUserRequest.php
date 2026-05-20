<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('user-update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                  => ['required'],
            'email'                 => ['required', 'email'],
            'address'               => ['nullable', 'string'],
            'primary_number'        => ['nullable', 'numeric'],
            'secondary_number'      => ['nullable', 'numeric'],
            'password'              => ['sometimes', 'min:6', 'confirmed'],
            'password_confirmation' => ['sometimes', 'min:6'],
            'image_path'            => ['nullable', 'file'],
            'role'                  => ['sometimes', 'integer', 'exists:roles,id'],
            'department'            => ['sometimes', 'integer', 'exists:departments,id'],
        ];
    }

    /**
     * Override the data used for validation so that password fields are excluded
     * when the authenticated user is not permitted to change the target user's password.
     */
    public function validationData(): array
    {
        $data = parent::validationData();

        if ( ! auth()->check()) {
            return $data;
        }

        $externalId = $this->route('user');
        $targetUser = $externalId
            ? User::where('external_id', $externalId)->first()
            : null;

        if ($targetUser && ! auth()->user()->canChangePasswordOn($targetUser)) {
            unset($data['password'], $data['password_confirmation']);
        }

        return $data;
    }
}
