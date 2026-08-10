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
}
