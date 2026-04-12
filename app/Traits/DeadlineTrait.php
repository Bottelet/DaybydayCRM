<?php

namespace App\Traits;

use Carbon\Carbon;

trait DeadlineTrait
{
    public function isOverDeadline(): bool
    {
        // If there's no deadline, it's not overdue
        if (!$this->deadline) {
            return false;
        }

        if ($this->isClosed()) {
            return false;
        }

        // Compare at start of day to handle both 'date' and 'datetime' casts
        return $this->deadline->startOfDay() < Carbon::now()->startOfDay();
    }

    public function isCloseToDeadline(int $days = 2): bool
    {
        return $this->deadline < Carbon::now()->addDays($days);
    }

    public function getDaysUntilDeadlineAttribute(): int
    {
        return Carbon::now()->startOfDay()->diffInDays($this->deadline, false);
    }
}
