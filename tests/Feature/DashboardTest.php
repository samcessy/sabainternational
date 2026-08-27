<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Page;
use App\Models\Story;
use App\Models\Supporter;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users without confirmed two-factor are redirected to security settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('security.edit'));
});

test('an editor sees content sections but not fundraising data', function () {
    Page::factory()->create(['title' => 'Awaiting Review', 'status' => ContentStatus::Review]);
    Donation::factory()->create(['status' => DonationStatus::Succeeded]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->has('pendingApprovals', 1)
        ->has('staleContent')
        ->missing('recentDonations')
    );
});

test('a finance manager sees recent donations but not content sections', function () {
    Donation::factory()->create(['status' => DonationStatus::Succeeded]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->has('recentDonations', 1)
        ->has('pendingApprovals')
    );
});

test('pending approvals lists content awaiting review with an edit link', function () {
    $page = Page::factory()->create(['title' => 'Needs Review', 'status' => ContentStatus::Review]);
    Page::factory()->create(['title' => 'Already Published', 'status' => ContentStatus::Published]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $inertiaPage) => $inertiaPage
        ->component('Dashboard')
        ->has('pendingApprovals', 1)
        ->where('pendingApprovals.0.title', 'Needs Review')
        ->where('pendingApprovals.0.href', route('admin.pages.edit', $page))
    );
});

test('a published story untouched for over 3 years is flagged as stale', function () {
    $stale = Story::factory()->published()->create(['title' => 'Old Story']);
    $stale->forceFill(['updated_at' => now()->subYears(4)])->saveQuietly();

    $fresh = Story::factory()->published()->create(['title' => 'Fresh Story']);

    $draftButOld = Story::factory()->create(['title' => 'Old Draft', 'status' => ContentStatus::Draft]);
    $draftButOld->forceFill(['updated_at' => now()->subYears(4)])->saveQuietly();

    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->has('staleContent', 1)
        ->where('staleContent.0.title', 'Old Story')
    );

    expect($fresh)->not->toBeNull();
});

test('recent donations shows the supporter name unless the donation is anonymous', function () {
    $supporter = Supporter::factory()->create(['name' => 'Jordan Donor']);
    Donation::factory()->create([
        'supporter_id' => $supporter->id,
        'status' => DonationStatus::Succeeded,
        'anonymous' => true,
    ]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('recentDonations.0.supporter_name', 'Anonymous')
    );
});

test('recent donations excludes donations that have not succeeded', function () {
    Donation::factory()->create(['status' => DonationStatus::Pending]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->has('recentDonations', 0)
    );
});
