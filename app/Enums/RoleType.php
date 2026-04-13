<?php

namespace App\Enums;

enum RoleType: string
{
    case OWNER = 'owner';
    case ADMINISTRATOR = 'administrator';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMINISTRATOR => 'Administrator',
            self::USER => 'User',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::OWNER || $this === self::ADMINISTRATOR;
    }

    public function canBeDeleted(): bool
    {
        return $this !== self::OWNER && $this !== self::ADMINISTRATOR;
    }

    public static function fromString(string $name): ?self
    {
        return match (strtolower($name)) {
            'owner' => self::OWNER,
            'administrator' => self::ADMINISTRATOR,
            'user' => self::USER,
            default => null,
        };
    }
}
