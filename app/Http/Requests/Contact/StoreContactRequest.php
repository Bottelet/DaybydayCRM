<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('contact-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                => 'required',
            'job_title'           => '',
            'email'               => 'email',
            'address1'            => '',
            'address2'            => '',
            'city'                => '',
            'state'               => '',
            'zipcode'             => 'max:6',
            'country'             => '',
            'primary_number'      => 'max:10',
            'secondary_number'    => 'max:10',
            'client_id'           => 'required',
        ];
    }
}
