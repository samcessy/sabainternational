<?php

use App\Enums\AdminRole;
use App\Enums\SubmissionStatus;
use App\Models\ContactSubmission;
use App\Models\PartnershipInquiry;
use App\Models\VolunteerApplication;
use Inertia\Testing\AssertableInertia as Assert;

// --- Contact Submissions ---

test('an editor can view contact messages', function () {
    ContactSubmission::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.contact-submissions.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/contact-submissions/Index')
        ->has('submissions.data', 1)
    );
});

test('a viewer can view but not update or delete a contact message', function () {
    $submission = ContactSubmission::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.contact-submissions.index'))->assertOk();
    $this->actingAs($viewer)
        ->put(route('admin.contact-submissions.update', $submission), ['status' => SubmissionStatus::Responded->value])
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.contact-submissions.destroy', $submission))->assertForbidden();
});

test('a finance manager cannot view contact messages at all', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.contact-submissions.index'))->assertForbidden();
});

test('guests are redirected to login for contact messages', function () {
    $this->get(route('admin.contact-submissions.index'))->assertRedirect(route('login'));
});

test('an editor can update a contact message status and it is audit logged', function () {
    $submission = ContactSubmission::factory()->create();
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->put(route('admin.contact-submissions.update', $submission), ['status' => SubmissionStatus::Responded->value])
        ->assertRedirect();

    $this->assertDatabaseHas('contact_submissions', ['id' => $submission->id, 'status' => 'responded']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'contact_submission',
        'entity_id' => $submission->id,
    ]);
});

test('an editor can delete a contact message and it is audit logged', function () {
    $submission = ContactSubmission::factory()->create();
    $editor = actingAsAdmin();

    $this->actingAs($editor)->delete(route('admin.contact-submissions.destroy', $submission))->assertRedirect();

    $this->assertDatabaseMissing('contact_submissions', ['id' => $submission->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'contact_submission',
        'entity_id' => $submission->id,
    ]);
});

// --- Volunteer Applications ---

test('an editor can view volunteer applications', function () {
    VolunteerApplication::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.volunteer-applications.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/volunteer-applications/Index')
        ->has('applications.data', 1)
    );
});

test('a finance manager cannot view volunteer applications', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.volunteer-applications.index'))->assertForbidden();
});

test('an editor can update a volunteer application status and it is audit logged', function () {
    $application = VolunteerApplication::factory()->create();
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->put(route('admin.volunteer-applications.update', $application), ['status' => SubmissionStatus::InProgress->value])
        ->assertRedirect();

    $this->assertDatabaseHas('volunteer_applications', ['id' => $application->id, 'status' => 'in_progress']);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'update',
        'entity_type' => 'volunteer_application',
        'entity_id' => $application->id,
    ]);
});

test('a viewer cannot update or delete a volunteer application', function () {
    $application = VolunteerApplication::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.volunteer-applications.update', $application), ['status' => SubmissionStatus::Closed->value])
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.volunteer-applications.destroy', $application))->assertForbidden();
});

// --- Partnership Inquiries ---

test('an editor can view partnership inquiries', function () {
    PartnershipInquiry::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.partnership-inquiries.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/partnership-inquiries/Index')
        ->has('inquiries.data', 1)
    );
});

test('a finance manager cannot view partnership inquiries', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.partnership-inquiries.index'))->assertForbidden();
});

test('an editor can delete a partnership inquiry and it is audit logged', function () {
    $inquiry = PartnershipInquiry::factory()->create();
    $editor = actingAsAdmin();

    $this->actingAs($editor)->delete(route('admin.partnership-inquiries.destroy', $inquiry))->assertRedirect();

    $this->assertDatabaseMissing('partnership_inquiries', ['id' => $inquiry->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'partnership_inquiry',
        'entity_id' => $inquiry->id,
    ]);
});

test('a viewer cannot update or delete a partnership inquiry', function () {
    $inquiry = PartnershipInquiry::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.partnership-inquiries.update', $inquiry), ['status' => SubmissionStatus::Spam->value])
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.partnership-inquiries.destroy', $inquiry))->assertForbidden();
});
