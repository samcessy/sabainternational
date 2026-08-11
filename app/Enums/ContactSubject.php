<?php

namespace App\Enums;

/**
 * ContactSubmission.subject dropdown per saba.md §23.1.
 */
enum ContactSubject: string
{
    case General = 'general';
    case Donation = 'donation';
    case Partnership = 'partnership';
    case Volunteer = 'volunteer';
    case Media = 'media';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Donation => 'Donation',
            self::Partnership => 'Partnership',
            self::Volunteer => 'Volunteer',
            self::Media => 'Media',
        };
    }
}
