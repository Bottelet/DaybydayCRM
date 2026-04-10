<?php

/** @var Factory $factory */

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factory;

class SettingFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Setting::class;

    public function definition()
    {
        return [
            'client_number' => 10000,
            'invoice_number' => 10000,
            'company' => 'test company',
            'max_users' => 10,

        ];
    }
}
