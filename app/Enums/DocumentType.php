<?php

namespace App\Enums;

enum DocumentType: string
{
    case AnnualReport = 'annual_report';
    case FinancialReport = 'financial_report';
    case Policy = 'policy';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::AnnualReport => 'Annual Report',
            self::FinancialReport => 'Financial Report',
            self::Policy => 'Policy',
            self::Other => 'Other',
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $type) => ['value' => $type->value, 'label' => $type->label()])
            ->values()
            ->all();
    }
}
