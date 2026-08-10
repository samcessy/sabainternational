<?php

namespace App\Enums;

/**
 * The 6 fixed image variants generated on upload. See
 * docs/architecture/media-architecture.md §1.
 */
enum MediaVariantType: string
{
    case Thumbnail = 'thumbnail';
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case Hero = 'hero';
    case Social = 'social';
}
