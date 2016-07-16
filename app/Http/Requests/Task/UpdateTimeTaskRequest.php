<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Request;

class UpdateTimeTaskRequest extends Request
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
            'title' => 'required',
            'comment' => '',
            'time' => 'required',
            'value' => 'required',
        ];
    }
}
