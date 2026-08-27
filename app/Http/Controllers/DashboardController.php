<?php

namespace App\Http\Controllers;

use App\Enums\AdminPermission;
use App\Enums\ContentStatus;
use App\Enums\DonationStatus;
use App\Models\Campaign;
use App\Models\Document;
use App\Models\Donation;
use App\Models\Event;
use App\Models\Page;
use App\Models\Program;
use App\Models\Story;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * saba.md §10.1: "Dashboard (content freshness alerts, pending approvals,
 * recent donations)". Each section is only queried when the signed-in
 * admin actually holds the permission that section depends on, so a
 * Viewer-role page load doesn't run fundraising queries it'll never render.
 */
class DashboardController extends Controller
{
    /**
     * Content types sharing the ContentStatus lifecycle (see its docblock),
     * each mapped to its admin edit route and title field.
     *
     * @var array<int, array{model: class-string<Model>, route: string, titleField: string, type: string}>
     */
    private const CONTENT_TYPES = [
        ['model' => Page::class, 'route' => 'admin.pages.edit', 'titleField' => 'title', 'type' => 'Page'],
        ['model' => Program::class, 'route' => 'admin.programs.edit', 'titleField' => 'name', 'type' => 'Program'],
        ['model' => Story::class, 'route' => 'admin.stories.edit', 'titleField' => 'title', 'type' => 'Story'],
        ['model' => Document::class, 'route' => 'admin.documents.edit', 'titleField' => 'title', 'type' => 'Document'],
        ['model' => Event::class, 'route' => 'admin.events.edit', 'titleField' => 'title', 'type' => 'Event'],
        ['model' => Campaign::class, 'route' => 'admin.campaigns.edit', 'titleField' => 'name', 'type' => 'Campaign'],
        ['model' => TeamMember::class, 'route' => 'admin.team-members.edit', 'titleField' => 'name', 'type' => 'Team Member'],
    ];

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $role = $user?->admin_role;

        $data = [];

        if ($role?->hasPermission(AdminPermission::ViewContent)) {
            $data['pendingApprovals'] = $this->pendingApprovals();
            $data['staleContent'] = $this->staleContent();
        }

        if ($role?->hasPermission(AdminPermission::ViewFundraising)) {
            $data['recentDonations'] = Donation::query()
                ->with('supporter')
                ->where('status', DonationStatus::Succeeded)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Donation $donation) => [
                    'id' => $donation->id,
                    'supporter_name' => $donation->anonymous ? 'Anonymous' : $donation->supporter->name,
                    'amount' => number_format($donation->amount_cents / 100, 2),
                    'currency' => $donation->currency,
                    'created_at' => $donation->created_at?->toIso8601String(),
                ])
                ->all();
        }

        return Inertia::render('Dashboard', $data);
    }

    /**
     * @return array<int, array{title: string, type: string, href: string, updated_at: string|null}>
     */
    private function pendingApprovals(): array
    {
        $rows = [];

        foreach (self::CONTENT_TYPES as $type) {
            /** @var class-string<Model> $model */
            $model = $type['model'];

            $records = $model::query()
                ->where('status', ContentStatus::Review)
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($records as $record) {
                $rows[] = [
                    'title' => (string) $record->getAttribute($type['titleField']),
                    'type' => $type['type'],
                    'href' => route($type['route'], $record),
                    'updated_at' => $this->formatTimestamp($record->getAttribute('updated_at')),
                ];
            }
        }

        usort($rows, fn (array $a, array $b) => ($b['updated_at'] ?? '') <=> ($a['updated_at'] ?? ''));

        return $rows;
    }

    /**
     * saba.md §16.3: stories older than 3 years without an update auto-flag
     * for review.
     *
     * @return array<int, array{title: string, href: string, updated_at: string|null}>
     */
    private function staleContent(): array
    {
        return Story::query()
            ->where('status', ContentStatus::Published)
            ->where('updated_at', '<', now()->subYears(3))
            ->orderBy('updated_at')
            ->limit(10)
            ->get()
            ->map(fn (Story $story) => [
                'title' => $story->title,
                'href' => route('admin.stories.edit', $story),
                'updated_at' => $story->updated_at?->toIso8601String(),
            ])
            ->all();
    }

    private function formatTimestamp(mixed $value): ?string
    {
        return $value instanceof Carbon ? $value->toIso8601String() : null;
    }
}
