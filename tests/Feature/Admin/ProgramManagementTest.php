<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ProgramRelationshipType;
use App\Enums\SensitiveContentClassification;
use App\Models\Program;
use Inertia\Testing\AssertableInertia as Assert;

function validProgramPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'New Dawn',
        'legal_name' => null,
        'slug' => 'new-dawn',
        'category' => ProgramCategory::Education->value,
        'relationship_type' => ProgramRelationshipType::OfficialProgram->value,
        'external_url' => null,
        'founded_year' => 2010,
        'location' => 'Nairobi, Kenya',
        'short_description' => 'An educational center.',
        'overview' => null,
        'what_happens_here' => null,
        'sensitive_content_classification' => SensitiveContentClassification::None->value,
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
        'status' => ContentStatus::Draft->value,
    ], $overrides);
}

test('an editor can view the programs index', function () {
    Program::factory()->create(['name' => 'A Program']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.programs.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/programs/Index')
        ->has('programs.data', 1)
    );
});

test('a viewer can view but not manage programs', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.programs.index'))->assertOk();
    $this->actingAs($viewer)->get(route('admin.programs.create'))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.programs.store'), validProgramPayload())->assertForbidden();
});

test('a finance manager cannot view or manage programs beyond viewAny', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.programs.index'))->assertOk();
    $this->actingAs($financeManager)->get(route('admin.programs.create'))->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.programs.index'))->assertRedirect(route('login'));
});

test('an editor can create a program and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.programs.store'), validProgramPayload());

    $response->assertRedirect(route('admin.programs.index'));
    $this->assertDatabaseHas('programs', ['slug' => 'new-dawn', 'name' => 'New Dawn']);

    $program = Program::query()->where('slug', 'new-dawn')->firstOrFail();
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'program',
        'entity_id' => $program->id,
    ]);
});

test('creating a program with status published sets published_at automatically', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.programs.store'), validProgramPayload([
        'status' => ContentStatus::Published->value,
    ]));

    $program = Program::query()->where('slug', 'new-dawn')->firstOrFail();
    expect($program->published_at)->not->toBeNull();
});

test('creating a program requires a unique slug', function () {
    Program::factory()->create(['slug' => 'new-dawn']);
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.programs.store'), validProgramPayload())
        ->assertSessionHasErrors('slug');
});

test('an editor can update a program and it is audit logged', function () {
    $program = Program::factory()->create(['name' => 'Old Name', 'slug' => 'old-slug']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.programs.update', $program),
        validProgramPayload(['name' => 'New Name', 'slug' => 'old-slug'])
    );

    $response->assertRedirect(route('admin.programs.index'));
    $this->assertDatabaseHas('programs', ['id' => $program->id, 'name' => 'New Name']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'program',
        'entity_id' => $program->id,
    ]);
});

test('updating a program keeps its own slug valid', function () {
    $program = Program::factory()->create(['slug' => 'new-dawn']);
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->put(route('admin.programs.update', $program), validProgramPayload(['slug' => 'new-dawn']))
        ->assertSessionDoesntHaveErrors('slug');
});

test('a viewer cannot update a program', function () {
    $program = Program::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.programs.update', $program), validProgramPayload())
        ->assertForbidden();
});

test('an editor can delete a program and it is audit logged', function () {
    $program = Program::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.programs.destroy', $program));

    $response->assertRedirect(route('admin.programs.index'));
    $this->assertSoftDeleted('programs', ['id' => $program->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'program',
        'entity_id' => $program->id,
    ]);
});

test('a viewer cannot delete a program', function () {
    $program = Program::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->delete(route('admin.programs.destroy', $program))->assertForbidden();
});
