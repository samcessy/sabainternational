<?php

use App\Enums\AdminRole;
use App\Models\AuditLog;
use App\Models\Program;
use Inertia\Testing\AssertableInertia as Assert;

test('a super administrator can view the audit log', function () {
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);
    AuditLog::factory()->create([
        'user_id' => $superAdmin->id,
        'action' => 'create',
        'entity_type' => 'program',
        'entity_id' => 1,
    ]);

    $response = $this->actingAs($superAdmin)->get(route('admin.audit-logs.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->has('auditLogs.data', 1)
        ->where('auditLogs.data.0.action', 'create')
        ->where('auditLogs.data.0.entity_type', 'program')
    );
});

test('an editor cannot view the audit log', function () {
    $editor = actingAsAdmin(AdminRole::Editor);

    $this->actingAs($editor)->get(route('admin.audit-logs.index'))->assertForbidden();
});

test('a finance manager cannot view the audit log', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.audit-logs.index'))->assertForbidden();
});

test('a viewer cannot view the audit log', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.audit-logs.index'))->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.audit-logs.index'))->assertRedirect(route('login'));
});

test('real admin actions taken this session actually show up in the log', function () {
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $this->actingAs($superAdmin)->post(route('admin.programs.store'), [
        'name' => 'New Dawn',
        'legal_name' => null,
        'slug' => 'new-dawn',
        'category' => 'education',
        'relationship_type' => 'official_program',
        'external_url' => null,
        'founded_year' => null,
        'location' => null,
        'short_description' => null,
        'overview' => null,
        'what_happens_here' => null,
        'sensitive_content_classification' => 'none',
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
        'status' => 'draft',
    ]);

    $program = Program::query()->where('slug', 'new-dawn')->firstOrFail();

    $response = $this->actingAs($superAdmin)->get(route('admin.audit-logs.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('auditLogs.data.0.action', 'create')
        ->where('auditLogs.data.0.entity_type', 'program')
        ->where('auditLogs.data.0.entity_id', $program->id)
        ->where('auditLogs.data.0.user_name', $superAdmin->name)
    );
});
