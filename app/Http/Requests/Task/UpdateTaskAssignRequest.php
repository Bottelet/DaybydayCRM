<?php

namespace App\Http\Requests\Task;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        return $user ? $user->can(PermissionName::TASK_UPDATE_ASSIGNMENT->value) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_assigned_id' => 'required|integer|exists:users,id',
        ];
    }
}
