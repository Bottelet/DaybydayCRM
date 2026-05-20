<?php

namespace App\Enums;

enum LeadStatus: string
{
    case OPEN        = 'open';
    case CLOSED      = 'closed';
    case PENDING     = 'pending';
    case IN_PROGRESS = 'in_progress';

    public static function isClosed(string $title): bool
    {
        return strcasecmp($title, self::CLOSED->value) === 0;
    }

    public function label(): string
    {
        return match ($this) {
            self::OPEN        => 'Open',
            self::CLOSED      => 'Closed',
            self::PENDING     => 'Pending',
            self::IN_PROGRESS => 'In Progress',
        };
    }
}
