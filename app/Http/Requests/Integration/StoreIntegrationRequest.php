<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegrationRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'api_type'      => ['required', 'string', 'in:billing,file'],
            'name'          => ['nullable', 'string', 'max:255'],
            'client_id'     => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:1000'],
            'api_key'       => ['nullable', 'string', 'max:1000'],
            'org_id'        => ['nullable', 'string', 'max:255'],
            'user_id'       => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
