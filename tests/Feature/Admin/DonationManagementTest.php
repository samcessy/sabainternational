<?php

use App\Enums\AdminRole;
use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Supporter;
use Inertia\Testing\AssertableInertia as Assert;

test('a finance manager can view the donations index', function () {
    $supporter = Supporter::factory()->create(['name' => 'Jane Donor']);
    $donation = Donation::factory()->for($supporter)->succeeded()->create();
    DonationTransaction::factory()->for($donation)->create();
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.donations.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/donations/Index')
        ->has('donations.data', 1)
        ->where('donations.data.0.supporter_name', 'Jane Donor')
        ->has('donations.data.0.transactions', 1)
        ->where('totals.succeeded_count', 1)
    );
});

test('an editor cannot view the donations index', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->get(route('admin.donations.index'))->assertForbidden();
});

test('a viewer can view donations (ViewFundraising is granted to Viewer)', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.donations.index'))->assertOk();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.donations.index'))->assertRedirect(route('login'));
});

test('a finance manager can export donations to csv and it is audit logged', function () {
    Donation::factory()->for(Supporter::factory())->succeeded()->create();
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.donations.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $financeManager->id,
        'action' => 'export-donor-data',
        'entity_type' => 'donation',
    ]);
});

test('a viewer cannot export donor data despite being able to view donations', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.donations.export'))->assertForbidden();
});

test('an editor cannot export donor data', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->get(route('admin.donations.export'))->assertForbidden();
});

test('anonymous donations still show the real donor identity to authorized staff', function () {
    $supporter = Supporter::factory()->create(['name' => 'Private Donor', 'email' => 'private@example.com']);
    Donation::factory()->for($supporter)->create(['anonymous' => true, 'status' => DonationStatus::Succeeded]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.donations.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('donations.data.0.supporter_name', 'Private Donor')
        ->where('donations.data.0.supporter_email', 'private@example.com')
        ->where('donations.data.0.anonymous', true)
    );
});
