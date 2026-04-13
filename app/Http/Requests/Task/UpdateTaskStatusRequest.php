<?php

namespace App\Http\Requests\Task;

use App\Enums\PermissionName;
use App\Models\Status;
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
        $user = auth()->user();

        return $user ? $user->can(PermissionName::TASK_UPDATE_STATUS->value) : false;
    }

    /**
     * Normalize input before validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('statusExternalId') && ! $this->has('status_id')) {
            $status = \App\Models\Status::whereExternalId($this->input('statusExternalId'))->first();
            if ($status) {
                $this->merge(['status_id' => $status->id]);
            }
        }
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

            // Validate status belongs to Task
            if ($statusId) {
                $validStatus = Status::typeOfTask()->where('id', $statusId)->exists();
                if (! $validStatus) {
                    $validator->errors()->add('status_id', __('Invalid status for task'));
                }
            }
        });
    }
}
