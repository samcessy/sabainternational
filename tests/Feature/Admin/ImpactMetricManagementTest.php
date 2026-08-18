<?php

use App\Enums\AdminRole;
use App\Enums\VerificationStatus;
use App\Models\ImpactMetric;
use App\Models\ImpactMetricValue;
use Inertia\Testing\AssertableInertia as Assert;

test('a viewer can view the impact metrics index', function () {
    ImpactMetric::factory()->create(['name' => 'Students Enrolled']);
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $response = $this->actingAs($viewer)->get(route('admin.impact-metrics.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/impact-metrics/Index')
        ->has('impactMetrics.data', 1)
    );
});

test('a finance manager cannot view impact metrics (impact data, not financial data)', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.impact-metrics.index'))->assertForbidden();
});

test('a viewer cannot create or manage impact metrics', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.impact-metrics.create'))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.impact-metrics.store'), [
        'name' => 'Students Enrolled',
        'unit' => 'students',
    ])->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.impact-metrics.index'))->assertRedirect(route('login'));
});

test('an editor can create an impact metric and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.impact-metrics.store'), [
        'name' => 'Students Enrolled',
        'unit' => 'students',
    ]);

    $response->assertRedirect(route('admin.impact-metrics.index'));
    $metric = ImpactMetric::query()->where('name', 'Students Enrolled')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'impact_metric',
        'entity_id' => $metric->id,
    ]);
});

test('the index shows only the latest verified value, not an unverified one', function () {
    $metric = ImpactMetric::factory()->create();
    ImpactMetricValue::factory()->for($metric, 'metric')->create(['value' => 999]); // unverified
    ImpactMetricValue::factory()->for($metric, 'metric')->verified()->create(['value' => 450]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.impact-metrics.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('impactMetrics.data.0.latest_verified_value', '450.00')
    );
});

test('an editor can record a new value on a metric and it is audit logged', function () {
    $metric = ImpactMetric::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.impact-metrics.values.store', $metric), [
        'value' => 450,
        'time_period' => '2026 School Year',
        'data_source' => 'Program records',
        'verification_status' => VerificationStatus::Verified->value,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('impact_metric_values', [
        'metric_id' => $metric->id,
        'time_period' => '2026 School Year',
        'verification_status' => 'verified',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'impact_metric_value',
    ]);
});

test('a viewer cannot record a new value on a metric', function () {
    $metric = ImpactMetric::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->post(route('admin.impact-metrics.values.store', $metric), [
        'value' => 450,
        'time_period' => '2026 School Year',
        'verification_status' => VerificationStatus::Verified->value,
    ])->assertForbidden();
});

test('an editor can delete a value and it is audit logged', function () {
    $metric = ImpactMetric::factory()->create();
    $value = ImpactMetricValue::factory()->for($metric, 'metric')->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.impact-metrics.values.destroy', [
        'impact_metric' => $metric->id,
        'value' => $value->id,
    ]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('impact_metric_values', ['id' => $value->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'impact_metric_value',
        'entity_id' => $value->id,
    ]);
});

test('an editor can delete a metric and it is audit logged', function () {
    $metric = ImpactMetric::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.impact-metrics.destroy', $metric));

    $response->assertRedirect(route('admin.impact-metrics.index'));
    $this->assertDatabaseMissing('impact_metrics', ['id' => $metric->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'impact_metric',
        'entity_id' => $metric->id,
    ]);
});
