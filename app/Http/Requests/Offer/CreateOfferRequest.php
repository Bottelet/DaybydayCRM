<?php

namespace App\Http\Requests\Offer;

use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;

class CreateOfferRequest extends FormRequest
{
    private const MAX_OFFER_LINES = 100;

    public function authorize()
    {
        return auth()->user()->can(PermissionName::OFFER_CREATE->value);
    }

    public function rules()
    {
        return [
            '*'          => 'array',
            '*.title'    => 'required|string',
            '*.type'     => 'required|string',
            '*.price'    => 'required|numeric',
            '*.quantity' => 'required|numeric|min:1',
            '*.comment'  => 'nullable|string',
            '*.product'  => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = $this->all();

            if ( ! is_array($lines)) {
                $validator->errors()->add('lines', __('Invalid payload format.'));

                return;
            }

            if (count($lines) > self::MAX_OFFER_LINES) {
                $validator->errors()->add('lines', __('Too many offer lines provided.'));
            }
        });
    }
}
