<?php

/** @var Factory $factory */

use App\Enums\OfferStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Offer::class, function (Faker $faker) {
    return [
        'client_id' => factory(Client::class),
        'status' => OfferStatus::inProgress()->getStatus(),
        'source_id' => factory(Lead::class),
        'source_type' => Lead::class,
    ];
});
