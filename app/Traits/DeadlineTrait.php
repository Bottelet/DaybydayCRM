<?php

namespace App\Traits;

use Carbon\Carbon;

trait DeadlineTrait
{
    public function isOverDeadline(): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        // If there's no deadline, it's not overdue
        if (!$this->deadline) {
            return false;
        }

        return $this->deadline < Carbon::now();
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
