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
}
