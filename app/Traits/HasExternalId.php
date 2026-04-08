<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasExternalId
{
    /**
     * Boot the HasExternalId trait for a model.
     *
     * @return void
     */
    public static function bootHasExternalId()
    {
        static::creating(function ($model) {
            if (empty($model->external_id) && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'external_id')) {
                $model->external_id = (string) Str::uuid();
            }
        });
    }
}
