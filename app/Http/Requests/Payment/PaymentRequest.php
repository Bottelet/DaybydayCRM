<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentSource;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('payment-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'regex:/^-?[0-9]+[.,]?[0-9]*+$/|required|not_in:0',
            'payment_date' => 'date|required',
            'source' => ['required', PaymentSource::validationRules()],
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
            'amount.integer' => __('The amount must be an integer.'),
            'amount.required' => __('The amount is required.'),
            'amount.not_in' => __('The amount can not be 0.'),
            'payment_date.date'  => __('The payment date is not a valid date.'),
            'payment_date.required'  => __('The payment date is required.'),
            'source.required' => __('The source is required.'),
            'source.in' => __('Invalid source'),
        ];
    }
}
