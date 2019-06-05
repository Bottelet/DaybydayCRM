<?php

namespace App\Http\Requests\Client;

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
            'name'              => 'required',
            'vat'               => 'max:12',
            'primary_email'     => 'email',
            'billing_address1'  => '',
            'billing_address2'  => '',
            'billing_city'      => '',
            'billing_state'     => '',
            'billing_zipcode'   => 'max:6',
            'billing_country'   => '',
            'shipping_address1' => '',
            'shipping_address2' => '',
            'shipping_city'     => '',
            'shipping_state'    => '',
            'shipping_zipcode'  => 'max:6',
            'shipping_country'  => '',
            'primary_number'    => 'max:10',
            'secondary_number'  => 'max:10',
            'industry_id'       => 'required',
            'company_type'      => '',
            'user_id'           => 'required',
        ];
    }
}
