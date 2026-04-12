<?php

namespace App\Http\Requests\Task;

use App\Enums\PermissionName;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can(PermissionName::TASK_UPDATE_STATUS->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status_id' => 'required_without:statusExternalId|integer|exists:statuses,id',
            'statusExternalId' => 'required_without:status_id|string|exists:statuses,external_id',
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
                    // Merge resolved status_id back into request
                    $this->merge(['status_id' => $statusId]);
                }
            }

            // Validate status belongs to Task
            if ($statusId) {
                $validStatus = Status::typeOfTask()->where('id', $statusId)->exists();
                if (!$validStatus) {
                    $validator->errors()->add('status_id', 'Invalid status for task');
                }
            }
        });
    }
}