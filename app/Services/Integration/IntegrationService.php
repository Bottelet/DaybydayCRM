<?php

namespace App\Services\Integration;

use App\Models\Integration;

class IntegrationService
{
    public function storeOrUpdateByApiType(array $data): Integration
    {
        $integration = Integration::query()->firstOrNew([
            'api_type' => $data['api_type'],
        ]);

        $integration->fill($data);
        $integration->save();

        return $integration;
    }
}
