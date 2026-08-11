<?php

namespace App\Enums;

/**
 * Shared by ContactSubmission, VolunteerApplication, and
 * PartnershipInquiry — all three are anonymous inbound submissions that
 * follow the same New → InProgress → Responded/Closed → Spam workflow
 * (saba.md §23.3).
 */
enum SubmissionStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Responded = 'responded';
    case Closed = 'closed';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::Responded => 'Responded',
            self::Closed => 'Closed',
            self::Spam => 'Spam',
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
