<?php

namespace App\Http\Requests\Task;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskDeadlineRequest extends FormRequest
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

        return $user->can(PermissionName::TASK_UPDATE_DEADLINE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'deadline_date' => 'required|date',
            'deadline_time' => 'nullable|string',
        ];
    }

    /**
     * Normalize deadline input before validation.
     * Combines deadline_date and deadline_time into a single deadline string (Y-m-d H:i:s).
     * Controller should use $request->validated('deadline').
     */
    protected function prepareForValidation()
    {
        $date = $this->input('deadline_date');
        $time = $this->input('deadline_time');
        if ($date) {
            $deadline = $date;
            if ($time) {
                $deadline .= ' '.$time;
            } else {
                $deadline .= ' 00:00:00';
            }
            $this->merge([
                'deadline' => $deadline,
            ]);
        }
    }
}
