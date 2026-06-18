<?php

namespace App\Http\Requests\Absence;

use App\Enums\AbsenceReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $validReasons = array_unique(
            array_map(fn ($r) => $r->getReason(), AbsenceReason::values())
        );

        return [
            'reason'               => ['required', 'string', Rule::in($validReasons)],
            'start_date'           => ['required', 'date'],
            'end_date'             => ['required', 'date', 'after_or_equal:start_date'],
            'user_external_id'     => ['nullable', 'string'],
            'medical_certificate'  => ['nullable', 'boolean'],
            'comment'              => ['nullable', 'string', 'max:2000'],
        ];
    }
}
