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
        return [
            'name' => 'required',
            'email' => 'required|email',
            'address' => '',
            'primary_number' => 'numeric',
            'secondary_number' => 'numeric',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'image_path' => '',
            'roles' => 'required',
            'departments' => 'required'
        ];
    }
}
