<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Media;
use Inertia\Testing\AssertableInertia as Assert;

function validCampaignPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Back to School 2026',
        'slug' => 'back-to-school-2026',
        'description' => null,
        'goal_amount' => null,
        'start_date' => null,
        'end_date' => null,
        'featured_image_media_id' => null,
        'impact_statement' => null,
        'suggested_amounts' => null,
        'status' => ContentStatus::Draft->value,
    ], $overrides);
}

test('a finance manager can view the campaigns index, including a real raised total', function () {
    $campaign = Campaign::factory()->create(['name' => 'General Fund']);
    Donation::factory()->for($campaign)->succeeded()->create(['amount_cents' => 5000]);
    Donation::factory()->for($campaign)->create(['amount_cents' => 9999]); // pending, excluded
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.campaigns.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/campaigns/Index')
        ->has('campaigns.data', 1)
        ->where('campaigns.data.0.raised_amount', '50.00')
    );
});

test('an editor cannot view campaigns (ViewFundraising, not ViewContent)', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->get(route('admin.campaigns.index'))->assertForbidden();
});

test('a viewer can view but not manage campaigns', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.campaigns.index'))->assertOk();
    $this->actingAs($viewer)->get(route('admin.campaigns.create'))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.campaigns.store'), validCampaignPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.campaigns.index'))->assertRedirect(route('login'));
});

test('a finance manager can create a campaign with dollar amounts converted to cents', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->post(route('admin.campaigns.store'), validCampaignPayload([
        'goal_amount' => '10000',
        'suggested_amounts' => '25, 50, 100',
    ]))->assertRedirect(route('admin.campaigns.index'));

    $campaign = Campaign::query()->where('slug', 'back-to-school-2026')->firstOrFail();
    expect($campaign->goal_amount_cents)->toBe(1000000)
        ->and($campaign->suggested_amounts)->toBe([2500, 5000, 10000]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $financeManager->id,
        'action' => 'create',
        'entity_type' => 'campaign',
        'entity_id' => $campaign->id,
    ]);
});

test('a campaign can be created with a featured image from the media library', function () {
    $media = Media::factory()->create();
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->post(route('admin.campaigns.store'), validCampaignPayload([
        'featured_image_media_id' => $media->id,
    ]));

    $this->assertDatabaseHas('campaigns', ['slug' => 'back-to-school-2026', 'featured_image_media_id' => $media->id]);
});

test('an end date before the start date fails validation', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)
        ->post(route('admin.campaigns.store'), validCampaignPayload([
            'start_date' => '2026-06-01',
            'end_date' => '2026-05-01',
        ]))
        ->assertSessionHasErrors('end_date');
});

test('an editor cannot create a campaign', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.campaigns.store'), validCampaignPayload())->assertForbidden();
});

test('editing a campaign converts cents back to dollar strings for the form', function () {
    $campaign = Campaign::factory()->create([
        'goal_amount_cents' => 1000000,
        'suggested_amounts' => [2500, 5000, 10000],
    ]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.campaigns.edit', $campaign));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('campaign.goal_amount', 10000)
        ->where('campaign.suggested_amounts', '25,50,100')
    );
});

test('a finance manager can delete a campaign and it is audit logged', function () {
    $campaign = Campaign::factory()->create();
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->delete(route('admin.campaigns.destroy', $campaign));

    $response->assertRedirect(route('admin.campaigns.index'));
    $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $financeManager->id,
        'action' => 'delete',
        'entity_type' => 'campaign',
        'entity_id' => $campaign->id,
    ]);
});
