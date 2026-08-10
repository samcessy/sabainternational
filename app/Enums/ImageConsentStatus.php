<?php

namespace App\Enums;

/**
 * Shared by Story.image_consent and Media.consent_status — same
 * yes/no/anonymized semantics in both places (docs/content-model.md).
 */
enum ImageConsentStatus: string
{
    case Yes = 'yes';
    case No = 'no';
    case Anonymized = 'anonymized';
}
