<?php

namespace App\Http\Requests\Setting;

use App\Repositories\Currency\Currency;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingOverallRequest extends FormRequest
{
    /** Fallback locales used when no lang files can be scanned from disk. */
    private const DEFAULT_LOCALES = ['en', 'dk'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('administrator') || $user?->hasRole('owner');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $validCurrencies = array_keys(Currency::getAllCurrencies());
        $validLanguages  = $this->availableLanguages();

        return [
            'client_number'  => ['required', 'integer', 'min:1'],
            'invoice_number' => ['required', 'integer', 'min:1'],
            'company'        => ['nullable', 'string', 'max:255'],
            'country'        => ['nullable', 'string', 'size:2'],
            'language'       => ['nullable', 'string', 'in:' . implode(',', $validLanguages)],
            'currency'       => ['nullable', 'string', 'in:' . implode(',', $validCurrencies)],
            'vat'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_time'     => ['nullable', 'date_format:H:i'],
            'end_time'       => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'client_number.required'  => __('Client number is required.'),
            'client_number.integer'   => __('Client number must be an integer.'),
            'client_number.min'       => __('Client number must be at least 1.'),
            'invoice_number.required' => __('Invoice number is required.'),
            'invoice_number.integer'  => __('Invoice number must be an integer.'),
            'invoice_number.min'      => __('Invoice number must be at least 1.'),
            'currency.in'             => __('The selected currency is invalid.'),
            'language.in'             => __('The selected language is invalid.'),
            'country.size'            => __('Country must be a 2-letter ISO code.'),
            'vat.numeric'             => __('VAT must be a number.'),
            'vat.min'                 => __('VAT must be at least 0.'),
            'vat.max'                 => __('VAT cannot exceed 100%.'),
            'start_time.date_format'  => __('Start time must be in HH:MM format.'),
            'end_time.date_format'    => __('End time must be in HH:MM format.'),
        ];
    }

    /**
     * Normalize fields before validation runs.
     *
     * - Currency codes are case-insensitive in user input; canonicalize to
     *   uppercase so the `in:` rule matches the stored currency list.
     * - Language codes are stored lowercase; ensure consistent casing.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('currency') && $this->currency !== null) {
            $merge['currency'] = mb_strtoupper($this->currency);
        }

        if ($this->has('language') && $this->language !== null) {
            $merge['language'] = mb_strtolower($this->language);
        }

        if ( ! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Derive the list of valid language codes from the translation files that
     * are actually present in the application, so adding a new locale
     * automatically makes it accepted without editing this class.
     *
     * Convention: a locale is considered available when either a JSON file
     * (e.g. `resources/lang/dk.json`) or a subdirectory
     * (e.g. `resources/lang/en/`) exists inside `resources/lang/`.
     */
    private function availableLanguages(): array
    {
        $langPath = resource_path('lang');

        if ( ! is_dir($langPath)) {
            return self::DEFAULT_LOCALES;
        }

        $locales = [];

        foreach (glob("{$langPath}/*.json") ?: [] as $file) {
            $locales[] = pathinfo($file, PATHINFO_FILENAME);
        }

        foreach (glob("{$langPath}/*/", GLOB_ONLYDIR) ?: [] as $dir) {
            $locales[] = basename(mb_rtrim($dir, '/'));
        }

        return ! empty($locales) ? array_unique($locales) : self::DEFAULT_LOCALES;
    }
}
