<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;

class UpdateLeadFollowUpRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'contact_date' => 'required',
            'contact_time' => 'required',
        ];
    }
}
