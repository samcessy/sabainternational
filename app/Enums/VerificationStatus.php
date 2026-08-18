<?php

namespace App\Enums;

/**
 * ImpactMetricValue.verification_status — an unverified/estimated value
 * never renders as a hard number on the public site (docs/content-model.md
 * §2.8's qualitative fallback rule, saba.md §6.3).
 */
enum VerificationStatus: string
{
    case Verified = 'verified';
    case Unverified = 'unverified';
    case Estimated = 'estimated';

    public function label(): string
    {
        return match ($this) {
            self::Verified => 'Verified',
            self::Unverified => 'Unverified',
            self::Estimated => 'Estimated',
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
