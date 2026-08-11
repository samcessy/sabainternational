<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class AboutPageController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('About', [
            // Sammy Tongoi (Draft, no bio — docs/audit/current-website-audit.md
            // F-9) is excluded by this query, not hidden by the frontend —
            // an unpublished team member never reaches the page at all.
            'teamMembers' => TeamMember::query()
                ->where('status', ContentStatus::Published)
                ->orderBy('display_order')
                ->get(['name', 'role', 'bio', 'board_member']),
        ]);
    }
}
