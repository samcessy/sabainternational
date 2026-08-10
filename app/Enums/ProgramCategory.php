<?php

namespace App\Enums;

/**
 * The four V1 program pillars. See docs/information-architecture.md §2 for
 * why this taxonomy was chosen over the live site's inconsistent tagging.
 */
enum ProgramCategory: string
{
    case Education = 'education';
    case Nutrition = 'nutrition';
    case ShelterFamilySupport = 'shelter_family_support';
    case YouthEconomicEmpowerment = 'youth_economic_empowerment';

    public function label(): string
    {
        return match ($this) {
            self::Education => 'Education',
            self::Nutrition => 'Nutrition',
            self::ShelterFamilySupport => 'Shelter & Family Support',
            self::YouthEconomicEmpowerment => 'Youth Economic Empowerment',
        };
    }
}
