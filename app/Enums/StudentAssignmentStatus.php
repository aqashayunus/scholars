<?php

namespace App\Enums;

enum StudentAssignmentStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Graded = 'graded';
    case Late = 'late';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
            self::Graded => 'Graded',
            self::Late => 'Late',
        };
    }
}
