<?php

use App\Models\ImpactMetric;
use App\Models\ImpactMetricValue;
use App\Models\Program;
use Inertia\Testing\AssertableInertia as Assert;

test('the homepage renders with no impact metrics when none are verified', function () {
    $metric = ImpactMetric::factory()->create();
    ImpactMetricValue::factory()->for($metric, 'metric')->create();

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->where('impactMetrics', [])
    );
});

test('the homepage shows only metrics with a verified value', function () {
    $verifiedMetric = ImpactMetric::factory()->create(['name' => 'Students Enrolled', 'unit' => 'students']);
    ImpactMetricValue::factory()->for($verifiedMetric, 'metric')->verified()->create(['value' => 120]);

    $unverifiedMetric = ImpactMetric::factory()->create(['name' => 'Meals Served']);
    ImpactMetricValue::factory()->for($unverifiedMetric, 'metric')->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->has('impactMetrics', 1)
        ->where('impactMetrics.0.name', 'Students Enrolled')
        ->where('impactMetrics.0.value', '120.00')
    );
});

test('a verified metric includes its program name when linked to one', function () {
    $program = Program::factory()->create(['name' => 'New Dawn']);
    $metric = ImpactMetric::factory()->create(['program_id' => $program->id]);
    ImpactMetricValue::factory()->for($metric, 'metric')->verified()->create();

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('impactMetrics.0.program', 'New Dawn')
    );
});

test('when a metric has multiple values, only the latest verified one is used', function () {
    $metric = ImpactMetric::factory()->create();
    ImpactMetricValue::factory()->for($metric, 'metric')->verified()->create([
        'value' => 100,
        'last_updated_at' => now()->subYear(),
    ]);
    ImpactMetricValue::factory()->for($metric, 'metric')->verified()->create([
        'value' => 200,
        'last_updated_at' => now(),
    ]);

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('impactMetrics', 1)
        ->where('impactMetrics.0.value', '200.00')
    );
});
