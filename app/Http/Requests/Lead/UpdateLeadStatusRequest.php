<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Keep existence checks in LeadsController::updateStatus()/LeadService::updateStatus() for the legacy redirect flow.
            'status_id' => ['nullable', 'integer', 'prohibits:closeLead,openLead'],
            'closeLead' => ['nullable', 'boolean', 'prohibits:status_id,openLead'],
            'openLead'  => ['nullable', 'boolean', 'prohibits:status_id,closeLead'],
        ];
    }
}
