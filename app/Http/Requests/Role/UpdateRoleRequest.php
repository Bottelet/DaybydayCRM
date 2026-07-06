<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorization handled by 'user.is.admin' middleware on RolesController
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
            'permissions'   => 'nullable|array',
            'permissions.*' => 'in:0,1',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Ensure permissions is an array. An empty array submitted over a form-encoded
        // request (e.g. permissions: [] from an HTTP client) serializes as an empty
        // string rather than being omitted, so checking only for null missed this case
        // and let a raw string reach RoleService::syncPermissions(), which requires array.
        if ( ! is_array($this->permissions)) {
            $this->merge(['permissions' => []]);
        }
    }
}
