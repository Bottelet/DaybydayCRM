<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;

class StoreLeadRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->canDo('lead.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'note' => 'required',
            'status' => 'required',
            'fk_user_id_assign' => 'required',
            'fk_user_id_created' => '',
            'fk_client_id' => 'required',
            'contact_date' => 'required'
        ];
    }
}
