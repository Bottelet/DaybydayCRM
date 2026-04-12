<?php

namespace App\Http\Requests\Project;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDeadlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(PermissionName::PROJECT_UPDATE_DEADLINE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'deadline' => 'required|date',
        ];
    }
}
