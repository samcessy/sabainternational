<?php

use App\Enums\ContentStatus;
use App\Models\Program;
use Inertia\Testing\AssertableInertia as Assert;

test('the give page renders with the stripe key and published programs', function () {
    config(['services.stripe.key' => 'pk_test_fake']);
    Program::factory()->create(['name' => 'Zebra Program', 'status' => ContentStatus::Published]);
    Program::factory()->create(['name' => 'Alpha Program', 'status' => ContentStatus::Published]);
    Program::factory()->create(['name' => 'Hidden Draft', 'status' => ContentStatus::Draft]);

    $response = $this->get(route('give.show'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Give')
        ->where('stripeKey', 'pk_test_fake')
        ->has('programs', 2)
        ->where('programs.0.name', 'Alpha Program')
    );
});

test('the give thank you page renders', function () {
    $this->get(route('give.thank-you'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('GiveThankYou'));
});
