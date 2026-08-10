<?php

namespace App\Enums;

/**
 * Shared by Program and Story per saba.md §7.3's content governance rules.
 */
enum SensitiveContentClassification: string
{
    case None = 'none';
    case Moderate = 'moderate';
    case High = 'high';
}
