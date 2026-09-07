<?php

namespace App\Enums;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Scheduled => 'warning',
            self::Published => 'success',
            self::Archived => 'danger',
        };
    }
}
