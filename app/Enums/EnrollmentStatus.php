<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Active = 'active';
    case Completed = 'completed';
    case Dropped = 'dropped';

    public function label(): string
    {
        return match ($this) {
            self::Enrolled => 'Enrolled',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Dropped => 'Dropped',
        };
    }
}
