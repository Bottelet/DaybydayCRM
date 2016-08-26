<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\Request;

class StoreClientRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('client-create');
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
            'company_name' => 'required',
            'vat' => 'max:12',
            'email' => 'required',
            'address' => '',
            'zipcode' => 'max:6',
            'city' => '',
            'primary_number' => 'max:10',
            'secondary_number' => 'max:10',
            'industry' => '',
            'company_type' => '',
            'fk_user_id' => 'required'
        ];
    }
}
