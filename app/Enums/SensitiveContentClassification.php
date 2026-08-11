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

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Moderate => 'Moderate',
            self::High => 'High',
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $classification) => ['value' => $classification->value, 'label' => $classification->label()])
            ->values()
            ->all();
    }
}
