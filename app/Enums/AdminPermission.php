<?php

namespace App\Enums;

/**
 * Granular admin permissions. See docs/architecture/authorization-model.md §3
 * for the full role → permission matrix this enum implements.
 */
enum AdminPermission: string
{
    case ViewContent = 'content:view';
    case ManageContent = 'content:manage';

    case ViewImpactData = 'impact:view';
    case ManageImpactData = 'impact:manage';

    case ViewEngagement = 'engagement:view';
    case ManageEngagement = 'engagement:manage';

    case ViewFundraising = 'fundraising:view';
    case ManageFundraising = 'fundraising:manage';
    case ExportDonorData = 'fundraising:export-donor-data';

    case ManageUsers = 'system:manage-users';
    case ManageSettings = 'system:manage-settings';
    case ViewAuditLogs = 'system:view-audit-logs';
}
