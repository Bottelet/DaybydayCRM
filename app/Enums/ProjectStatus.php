<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case OPEN        = 'open';
    case CLOSED      = 'Closed'; // Note: Uses capital C to match existing data
    case IN_PROGRESS = 'in_progress';
    case PENDING     = 'pending';

    public static function isClosed(string $title): bool
    {
        return strcasecmp($title, self::CLOSED->value) === 0 || strcasecmp($title, 'closed') === 0;
    }

    public function label(): string
    {
        return match ($this) {
            self::OPEN        => 'Open',
            self::CLOSED      => 'Closed',
            self::IN_PROGRESS => 'In Progress',
            self::PENDING     => 'Pending',
        };
    }
}
