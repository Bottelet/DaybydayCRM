<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Enums\OfferStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

class OfferFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Offer::class;

    public function definition()
    {
        return [
            'external_id' => Uuid::uuid4()->toString(),
            'client_id' => Client::factory(),
            'status' => OfferStatus::inProgress()->getStatus(),
            'source_id' => Lead::factory(),
            'source_type' => Lead::class,
        ];
    }
}
