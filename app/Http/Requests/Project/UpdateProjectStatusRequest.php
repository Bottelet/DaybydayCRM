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
            'status_id'        => 'sometimes|integer|exists:statuses,id|required_without:statusExternalId',
            'statusExternalId' => 'sometimes|string|exists:statuses,external_id|required_without:status_id',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $statusId = $this->status_id;

            // Validate status belongs to Project
            if ($statusId) {
                $validStatus = Status::typeOfProject()->where('id', $statusId)->exists();
                if ( ! $validStatus) {
                    $validator->errors()->add('status_id', __('Invalid status for project'));
                }
            }
        });
    }

    /**
     * Normalize input before validation.
     *
     * Resolving statusExternalId -> status_id here (rather than in withValidator's
     * after() hook) matters: the Validator snapshots its data when it's built, so a
     * merge() done inside after() never reaches validated() — validated('status_id')
     * would come back null even though the external id resolved correctly, and the
     * controller would then try to save a null status_id.
     */
    protected function prepareForValidation()
    {
        if ($this->has('statusExternalId') && ! $this->has('status_id')) {
            $status = Status::whereExternalId($this->input('statusExternalId'))->first();
            if ($status) {
                $this->merge(['status_id' => $status->id]);
            }
        }
    }
}
