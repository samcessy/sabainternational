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
}
