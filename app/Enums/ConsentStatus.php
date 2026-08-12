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

    public function label(): string
    {
        return match ($this) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::Guardian => 'Guardian Consent',
            self::NotRequired => 'Not Required',
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
