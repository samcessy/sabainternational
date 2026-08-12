<?php

namespace App\Enums;

/**
 * Story.approval_stage — the draft → review → approval workflow required by
 * saba.md §7.3, distinct from the simpler ContentStatus lifecycle other
 * content types use.
 */
enum ApprovalStage: string
{
    case Draft = 'draft';
    case EditorReview = 'editor_review';
    case AdminApproval = 'admin_approval';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::EditorReview => 'Editor Review',
            self::AdminApproval => 'Admin Approval',
            self::Published => 'Published',
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $stage) => ['value' => $stage->value, 'label' => $stage->label()])
            ->values()
            ->all();
    }
}
