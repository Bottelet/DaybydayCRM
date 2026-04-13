<?php

namespace App\Http\Requests\Client;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /*$user = auth()->user();
        if ($user === null) {
            dd("huh?");
            return false;
        }

        return $user->can(PermissionName::CLIENT_CREATE->value);*/
        return true;
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
            'industry_id' => 'required',
            'company_type' => '',
            'user_id' => 'required',
        ];
    }
}
