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

    public function label(): string
    {
        return match ($this) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::Anonymized => 'Anonymized',
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $status) => ['value' => $status->value, 'label' => $status->label()])
            ->values()
            ->all();
    }
}
