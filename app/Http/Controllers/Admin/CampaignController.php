<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Http\Requests\Admin\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Campaign::class);

        return Inertia::render('admin/campaigns/Index', [
            'campaigns' => Campaign::query()
                ->latest()
                ->paginate(20)
                ->through(fn (Campaign $campaign) => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'slug' => $campaign->slug,
                    'goal_amount' => $campaign->goal_amount_cents ? number_format($campaign->goal_amount_cents / 100, 2) : null,
                    'raised_amount' => number_format($campaign->donations()->where('status', 'succeeded')->sum('amount_cents') / 100, 2),
                    'status' => $campaign->status->value,
                    'status_label' => $campaign->status->label(),
                    'end_date' => $campaign->end_date?->toDateString(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Campaign::class);

        return Inertia::render('admin/campaigns/Create', [
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $data = $this->transformAmounts($request->validated());

        $campaign = Campaign::create($data);

        $this->auditLogger->log($request->user(), 'create', $campaign, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$campaign->name}\" was created.",
        ]);

        return to_route('admin.campaigns.index');
    }

    public function edit(Campaign $campaign): Response
    {
        $this->authorize('update', $campaign);

        return Inertia::render('admin/campaigns/Edit', [
            'campaign' => [
                ...$campaign->only([
                    'id', 'name', 'slug', 'description', 'start_date', 'end_date',
                    'featured_image_media_id', 'impact_statement', 'status',
                ]),
                'goal_amount' => $campaign->goal_amount_cents ? $campaign->goal_amount_cents / 100 : null,
                'suggested_amounts' => $campaign->suggested_amounts
                    ? implode(',', array_map(fn (int $cents) => $cents / 100, $campaign->suggested_amounts))
                    : null,
                'start_date' => $campaign->start_date?->toDateString(),
                'end_date' => $campaign->end_date?->toDateString(),
                'featured_image_thumbnail_url' => $campaign->featuredImage?->thumbnailUrl(),
            ],
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $oldValues = $campaign->only(array_keys($request->validated()));
        $data = $this->transformAmounts($request->validated());

        $campaign->update($data);

        $this->auditLogger->log($request->user(), 'update', $campaign, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$campaign->name}\" was updated.",
        ]);

        return to_route('admin.campaigns.index');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);

        $name = $campaign->name;
        $this->auditLogger->log(request()->user(), 'delete', $campaign, oldValues: $campaign->only(['name', 'slug', 'status']));
        $campaign->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$name}\" was deleted.",
        ]);

        return to_route('admin.campaigns.index');
    }

    /**
     * Converts the form-facing dollar amounts (goal_amount, a plain
     * number; suggested_amounts, a comma-separated string) into the
     * integer-cents shapes the schema actually stores, matching how every
     * other money field in this app is represented
     * (docs/architecture/payment-architecture.md §8).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function transformAmounts(array $data): array
    {
        $goalAmount = $data['goal_amount'] ?? null;
        unset($data['goal_amount']);
        $data['goal_amount_cents'] = $goalAmount !== null && $goalAmount !== ''
            ? (int) round((float) $goalAmount * 100)
            : null;

        $suggestedAmounts = $data['suggested_amounts'] ?? null;
        $data['suggested_amounts'] = $suggestedAmounts
            ? collect(explode(',', $suggestedAmounts))
                ->map(fn (string $amount) => trim($amount))
                ->filter(fn (string $amount) => is_numeric($amount))
                ->map(fn (string $amount) => (int) round((float) $amount * 100))
                ->values()
                ->all()
            : null;

        return $data;
    }
}
