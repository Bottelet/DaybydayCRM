<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('user-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // "role"/"department" (singular) match both the actual <select> field names in
        // the shared users/form.blade.php partial and UpdateUserRequest's rules. This
        // used to require "roles"/"departments" (plural), which the form never sent —
        // every real user-creation submission silently failed validation.
        return [
            'name'                  => ['required'],
            'email'                 => ['required', 'email'],
            'address'               => ['nullable', 'string'],
            'primary_number'        => ['nullable', 'numeric'],
            'secondary_number'      => ['nullable', 'numeric'],
            'password'              => ['required', 'min:6', 'confirmed'],
            'password_confirmation' => ['required', 'min:6'],
            'image_path'            => ['nullable', 'file'],
            'role'                  => ['required', 'integer', 'exists:roles,id'],
            'department'            => ['required', 'integer', 'exists:departments,id'],
            'language'              => ['nullable', 'string', 'in:en,dk,es'],
        ];
    }
}
