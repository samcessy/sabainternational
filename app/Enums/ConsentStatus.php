<?php

namespace App\Enums;

/**
 * Story.consent_status — required before a story depicting a real person
 * can publish. See docs/content-model.md §2.4.
 */
enum ConsentStatus: string
{
    case Yes = 'yes';
    case No = 'no';
    case Guardian = 'guardian';
    case NotRequired = 'not_required';
}
