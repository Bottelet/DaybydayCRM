<?php

namespace App\Http\Requests\Project;

use App\Enums\PermissionName;
use App\Models\Status;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(PermissionName::PROJECT_UPDATE_STATUS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status_id' => 'sometimes|required|integer|exists:statuses,id',
            'statusExternalId' => 'sometimes|required|string|exists:statuses,external_id',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $statusId = $this->status_id;
            
            // Convert external ID to ID if provided
            if ($this->statusExternalId && !$statusId) {
                $status = Status::whereExternalId($this->statusExternalId)->first();
                if ($status) {
                    $statusId = $status->id;
                }
            }
            
            // Validate status belongs to Project
            if ($statusId) {
                $validStatus = Status::typeOfProject()->where('id', $statusId)->exists();
                if (!$validStatus) {
                    $validator->errors()->add('status_id', 'Invalid status for project');
                }
            }
        });
    }
}
