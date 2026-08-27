<?php

use App\Enums\AdminRole;
use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Supporter;
use Inertia\Testing\AssertableInertia as Assert;

test('a finance manager can view the supporters index with totals', function () {
    $supporter = Supporter::factory()->create(['name' => 'Jane Donor']);
    Donation::factory()->for($supporter)->create(['status' => DonationStatus::Succeeded, 'amount_cents' => 5000]);
    Donation::factory()->for($supporter)->create(['status' => DonationStatus::Succeeded, 'amount_cents' => 2500]);
    Donation::factory()->for($supporter)->create(['status' => DonationStatus::Failed, 'amount_cents' => 10000]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.supporters.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/supporters/Index')
        ->has('supporters.data', 1)
        ->where('supporters.data.0.name', 'Jane Donor')
        ->where('supporters.data.0.donations_count', 3)
        ->where('supporters.data.0.total_donated', '75.00')
    );
});

test('an editor cannot view the supporters index', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->get(route('admin.supporters.index'))->assertForbidden();
});

test('a viewer can view supporters (ViewFundraising is granted to Viewer)', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.supporters.index'))->assertOk();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.supporters.index'))->assertRedirect(route('login'));
});

test('a finance manager can view a supporter and their donation history', function () {
    $supporter = Supporter::factory()->create(['name' => 'Jane Donor']);
    Donation::factory()->for($supporter)->create(['status' => DonationStatus::Succeeded, 'amount_cents' => 5000]);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.supporters.show', $supporter));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/supporters/Show')
        ->where('supporter.name', 'Jane Donor')
        ->has('donations', 1)
    );
});

test('a finance manager can update a supporter and it is audit logged', function () {
    $supporter = Supporter::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->put(route('admin.supporters.update', $supporter), [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect(route('admin.supporters.show', $supporter));
    $this->assertDatabaseHas('supporters', ['id' => $supporter->id, 'name' => 'New Name', 'email' => 'new@example.com']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $financeManager->id,
        'action' => 'update',
        'entity_type' => 'supporter',
        'entity_id' => $supporter->id,
    ]);
});

test('a duplicate email fails validation on update', function () {
    Supporter::factory()->create(['email' => 'taken@example.com']);
    $supporter = Supporter::factory()->create(['email' => 'mine@example.com']);
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)
        ->put(route('admin.supporters.update', $supporter), ['name' => 'Name', 'email' => 'taken@example.com'])
        ->assertSessionHasErrors('email');
});

test('a viewer cannot update a supporter', function () {
    $supporter = Supporter::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.supporters.update', $supporter), ['name' => 'x', 'email' => 'x@example.com'])
        ->assertForbidden();
});

test('a finance manager can export supporters to csv and it is audit logged', function () {
    Supporter::factory()->create();
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $response = $this->actingAs($financeManager)->get(route('admin.supporters.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $financeManager->id,
        'action' => 'export-donor-data',
        'entity_type' => 'supporter',
    ]);
});

test('a viewer cannot export supporter data despite being able to view supporters', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.supporters.export'))->assertForbidden();
});

test('an editor cannot export supporter data', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->get(route('admin.supporters.export'))->assertForbidden();
});
