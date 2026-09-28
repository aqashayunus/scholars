<?php

namespace App\Enums;

enum LiveClassStatus: string
{
    case Upcoming = 'upcoming';
    case Live = 'live';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Upcoming',
            self::Live => 'Live',
            self::Completed => 'Completed',
        };
    }
}
