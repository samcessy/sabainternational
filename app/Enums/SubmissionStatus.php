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
}
