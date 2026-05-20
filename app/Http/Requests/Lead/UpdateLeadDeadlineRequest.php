<?php

namespace App\Http\Requests\Lead;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadDeadlineRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->can(PermissionName::LEAD_UPDATE_DEADLINE->value);
    }

    public function rules()
    {
        return [
            'deadline_date' => ['required', 'date_format:Y-m-d'],
            'deadline_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
