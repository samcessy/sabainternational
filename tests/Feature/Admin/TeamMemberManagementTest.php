<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\TeamMember;
use Inertia\Testing\AssertableInertia as Assert;

function validTeamMemberPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Doe',
        'role' => 'Program Coordinator',
        'bio' => 'Jane has worked in education for a decade.',
        'board_member' => false,
        'consent_to_publish' => true,
        'display_order' => 0,
        'status' => ContentStatus::Draft->value,
    ], $overrides);
}

test('an editor can view the team members index', function () {
    TeamMember::factory()->create(['name' => 'A Team Member']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.team-members.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/team-members/Index')
        ->has('teamMembers.data', 1)
    );
});

test('a viewer can view but not manage team members', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.team-members.index'))->assertOk();
    $this->actingAs($viewer)->get(route('admin.team-members.create'))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.team-members.store'), validTeamMemberPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.team-members.index'))->assertRedirect(route('login'));
});

test('an editor can create a team member and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.team-members.store'), validTeamMemberPayload());

    $response->assertRedirect(route('admin.team-members.index'));
    $teamMember = TeamMember::query()->where('name', 'Jane Doe')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'team_member',
        'entity_id' => $teamMember->id,
    ]);
});

test('publishing a team member without a bio is a validation error, not a crash', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.team-members.store'), validTeamMemberPayload([
            'bio' => null,
            'status' => ContentStatus::Published->value,
        ]))
        ->assertSessionHasErrors('bio');

    $this->assertDatabaseMissing('team_members', ['name' => 'Jane Doe']);
});

test('unchecking board_member on update actually clears it, not just omits it', function () {
    $teamMember = TeamMember::factory()->create(['board_member' => true]);
    $editor = actingAsAdmin();

    $payload = validTeamMemberPayload();
    unset($payload['board_member']);

    $this->actingAs($editor)
        ->put(route('admin.team-members.update', $teamMember), $payload)
        ->assertRedirect(route('admin.team-members.index'));

    expect($teamMember->fresh()->board_member)->toBeFalse();
});

test('an editor can update a team member and it is audit logged', function () {
    $teamMember = TeamMember::factory()->create(['name' => 'Old Name']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.team-members.update', $teamMember),
        validTeamMemberPayload(['name' => 'New Name'])
    );

    $response->assertRedirect(route('admin.team-members.index'));
    $this->assertDatabaseHas('team_members', ['id' => $teamMember->id, 'name' => 'New Name']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'team_member',
        'entity_id' => $teamMember->id,
    ]);
});

test('a viewer cannot update or delete a team member', function () {
    $teamMember = TeamMember::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.team-members.update', $teamMember), validTeamMemberPayload())
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.team-members.destroy', $teamMember))->assertForbidden();
});

test('an editor can delete a team member and it is audit logged', function () {
    $teamMember = TeamMember::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.team-members.destroy', $teamMember));

    $response->assertRedirect(route('admin.team-members.index'));
    $this->assertSoftDeleted('team_members', ['id' => $teamMember->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'team_member',
        'entity_id' => $teamMember->id,
    ]);
});

test('a team member can be created with a photo from the media library', function () {
    $media = Media::factory()->create();
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.team-members.store'), validTeamMemberPayload([
        'photo_media_id' => $media->id,
    ]))->assertRedirect(route('admin.team-members.index'));

    $this->assertDatabaseHas('team_members', ['name' => 'Jane Doe', 'photo_media_id' => $media->id]);
});

test('creating a team member with a nonexistent photo id fails validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.team-members.store'), validTeamMemberPayload(['photo_media_id' => 99999]))
        ->assertSessionHasErrors('photo_media_id');
});

test('an unselected photo does not fail exists validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.team-members.store'), [...validTeamMemberPayload(), 'photo_media_id' => ''])
        ->assertSessionDoesntHaveErrors('photo_media_id');
});

test('editing a team member exposes its photo thumbnail for the picker preview', function () {
    $media = Media::factory()->create();
    $teamMember = TeamMember::factory()->create(['photo_media_id' => $media->id]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.team-members.edit', $teamMember));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('teamMember.photo_media_id', $media->id)
    );
});
