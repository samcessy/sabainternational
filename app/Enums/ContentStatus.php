<?php

namespace App\Enums;

/**
 * Shared editorial lifecycle for Page, Program, TeamMember, Story, Document,
 * Event, and Campaign. See docs/content-model.md's publishing workflow.
 */
enum ContentStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'In Review',
            self::Published => 'Published',
            self::Archived => 'Archived',
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
