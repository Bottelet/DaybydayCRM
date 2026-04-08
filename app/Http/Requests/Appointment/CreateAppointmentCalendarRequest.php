<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentCalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can("appointment-create");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'user' => 'required',
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'color' => 'required'
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => __('The title is required.'),
            'user.required' => __('The user is required.'),
            'start_time.date'  => __('The start date is not a valid date.'),
            'end_date.date'  => __('The end date is required.'),
            'start_time.required' => __('The start time is required.'),
            'end_time.required' => __('The end time is required.'),
            'color.required' => __('The color is required.'),
        ];
    }
}
