<?php

namespace App\Http\Requests\Project;

use App\Models\Status;
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
        return auth()->check() && auth()->user()->can('project-update-status');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status_id' => 'sometimes|integer|exists:statuses,id|required_without:statusExternalId',
            'statusExternalId' => 'sometimes|string|exists:statuses,external_id|required_without:status_id',
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
            if ($this->statusExternalId && ! $statusId) {
                $status = Status::whereExternalId($this->statusExternalId)->first();
                if ($status) {
                    $statusId = $status->id;
                    // Merge resolved status_id back into request
                    $this->merge(['status_id' => $statusId]);
                }
            }

            // Validate status belongs to Project
            if ($statusId) {
                $validStatus = Status::typeOfProject()->where('id', $statusId)->exists();
                if (! $validStatus) {
                    $validator->errors()->add('status_id', __('Invalid status for project'));
                }
            }
        });
    }
}
