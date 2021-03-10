<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\OfferStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Offer;
use Faker\Generator as Faker;
use Ramsey\Uuid\Uuid;

$factory->define(Offer::class, function (Faker $faker) {
    return [
        'external_id' => Uuid::uuid4()->toString(),
        'client_id' => factory(Client::class)->create()->id,
        'status' => OfferStatus::inProgress()->getStatus(),
        'source_id' => factory(Lead::class)->create()->id,
        'source_type' => Lead::class,
    ];
});
