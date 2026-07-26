<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait HasExternalId.
 *
 * Automatically generates a UUID for the external_id field when creating models.
 * This trait ensures consistent UUID generation across all models that use external_id.
 */
trait HasExternalId
{
    public static function bootHasExternalId()
    {
        static::creating(function ($model) {
            if (empty($model->external_id) && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'external_id')) {
                $model->external_id = (string) Str::uuid();
            }
        });
    }

    public static function findByExternalId(?string $externalId): ?static
    {
        if ($externalId === null) {
            return null;
        }

        return static::query()->where('external_id', $externalId)->first();
    }

    public static function whereExternalId(?string $externalId)
    {
        return static::query()->where('external_id', $externalId);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'external_id';
    }
}
