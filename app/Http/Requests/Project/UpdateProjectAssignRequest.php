<?php

namespace App\Http\Requests\Project;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectAssignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        if ($user === null) {
            return false;
        }
        return $user->can('can-assign-new-user-to-project');
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