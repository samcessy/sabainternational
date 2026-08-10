<?php

namespace App\Enums;

/**
 * The 9 story types from saba.md §7.1. Schema supports all 9 from day one;
 * V1 only seeds real content for whichever types verified stories exist for
 * (docs/product-requirements.md §4).
 */
enum StoryType: string
{
    case StoryOfChange = 'story_of_change';
    case ProgramUpdate = 'program_update';
    case News = 'news';
    case VolunteerStory = 'volunteer_story';
    case DonorStory = 'donor_story';
    case PartnerStory = 'partner_story';
    case FounderStory = 'founder_story';
    case YouthStory = 'youth_story';
    case CommunityStory = 'community_story';
}
