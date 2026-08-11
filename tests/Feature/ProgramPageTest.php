<?php

use App\Enums\ContentStatus;
use App\Models\Program;
use Inertia\Testing\AssertableInertia as Assert;

test('the programs index renders only published programs', function () {
    Program::factory()->published()->create(['name' => 'Zebra Program']);
    Program::factory()->published()->create(['name' => 'Alpha Program']);
    Program::factory()->create(['name' => 'Hidden Draft', 'status' => ContentStatus::Draft]);

    $response = $this->get(route('programs.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('programs/Index')
        ->has('programs', 2)
        ->where('programs.0.name', 'Alpha Program')
    );
});

test('a published program show page renders by slug', function () {
    Program::factory()->published()->create(['slug' => 'new-dawn', 'name' => 'New Dawn']);

    $response = $this->get(route('programs.show', 'new-dawn'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('programs/Show')
        ->where('program.slug', 'new-dawn')
    );
});

test('a draft program returns 404 on its show page', function () {
    Program::factory()->create(['slug' => 'draft-program', 'status' => ContentStatus::Draft]);

    $this->get(route('programs.show', 'draft-program'))->assertNotFound();
});

test('a nonexistent program slug returns 404', function () {
    $this->get(route('programs.show', 'does-not-exist'))->assertNotFound();
});
