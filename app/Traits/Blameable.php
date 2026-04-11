<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Blameable Trait
 *
 * Automatically populates user_created_id and user_updated_id fields
 * for models that track who created and updated records.
 *
 * Usage:
 * 1. Add the trait to your model: use Blameable;
 * 2. Ensure your model has 'user_created_id' and 'user_updated_id' columns
 * 3. Add these fields to $fillable if needed
 *
 * Example migration:
 * $table->integer('user_created_id')->unsigned()->nullable();
 * $table->foreign('user_created_id')->references('id')->on('users');
 * $table->integer('user_updated_id')->unsigned()->nullable();
 * $table->foreign('user_updated_id')->references('id')->on('users');
 */
trait Blameable
{
    /**
     * Boot the blameable trait for a model.
     *
     * @return void
     */
    public static function bootBlameable()
    {
        // Set user_created_id when creating a new record
        static::creating(function ($model) {
            if (Auth::check() && ! $model->user_created_id) {
                $model->user_created_id = Auth::id();
            }

            // Also set user_updated_id on creation
            if (Auth::check() && ! $model->user_updated_id) {
                $model->user_updated_id = Auth::id();
            }
        });

        // Update user_updated_id when updating a record
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->user_updated_id = Auth::id();
            }
        });
    }

    /**
     * Get the user who created this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_created_id');
    }

    /**
     * Get the user who last updated this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_updated_id');
    }
}
