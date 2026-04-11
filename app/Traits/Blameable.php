<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait Blameable
 * 
 * Automatically sets the user_created_id field when creating a model.
 * Models using this trait should have a user_created_id column.
 */
trait Blameable
{
    protected static function bootBlameable()
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->user_created_id)) {
                $model->user_created_id = Auth::id();
            }
        });
    }
}
