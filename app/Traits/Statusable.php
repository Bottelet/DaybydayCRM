<?php

namespace App\Traits;

use App\Models\Status;

/**
 * Statusable Trait
 *
 * Provides common status-related methods for models that have a status relationship.
 *
 * Usage:
 * 1. Add the trait to your model: use Statusable;
 * 2. Ensure your model has a 'status_id' column
 * 3. The trait provides the status() relationship and helper methods
 *
 * Example migration:
 * $table->integer('status_id')->unsigned();
 * $table->foreign('status_id')->references('id')->on('statuses');
 */
trait Statusable
{
    /**
     * Get the status of this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Check if the model has a specific status.
     *
     * @param  string  $statusTitle  The title of the status to check
     */
    public function hasStatus(string $statusTitle): bool
    {
        return $this->status && $this->status->title === $statusTitle;
    }

    /**
     * Set the status of this model by status title.
     */
    public function setStatus(string $statusTitle): bool
    {
        $status = Status::where('title', $statusTitle)->first();

        if ($status) {
            $this->status_id = $status->id;

            return $this->save();
        }

        return false;
    }

    /**
     * Scope a query to only include models with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, string $statusTitle)
    {
        return $query->whereHas('status', function ($q) use ($statusTitle) {
            $q->where('title', $statusTitle);
        });
    }

    /**
     * Scope a query to exclude models with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutStatus($query, string $statusTitle)
    {
        return $query->whereHas('status', function ($q) use ($statusTitle) {
            $q->where('title', '!=', $statusTitle);
        });
    }
}
