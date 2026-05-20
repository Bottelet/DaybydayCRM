<?php

namespace Database\Factories;

use App\Models\BusinessHour;

class BusinessHourFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = BusinessHour::class;

    public function definition()
    {
        return [
            'day'        => 'monday',
            'open_time'  => '09:00',
            'close_time' => '18:00',
        ];
    }
}
