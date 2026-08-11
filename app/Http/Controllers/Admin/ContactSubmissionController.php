<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactSubmissionStatusRequest;
use App\Models\ContactSubmission;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactSubmissionController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', ContactSubmission::class);

        return Inertia::render('admin/contact-submissions/Index', [
            'submissions' => ContactSubmission::query()
                ->latest()
                ->paginate(20)
                ->through(fn (ContactSubmission $submission) => [
                    'id' => $submission->id,
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'country' => $submission->country,
                    'organization' => $submission->organization,
                    'subject_label' => $submission->subject->label(),
                    'message' => $submission->message,
                    'status' => $submission->status->value,
                    'status_label' => $submission->status->label(),
                    'created_at' => $submission->created_at?->toIso8601String(),
                ]),
            'statusOptions' => SubmissionStatus::options(),
        ]);
    }

    public function update(UpdateContactSubmissionStatusRequest $request, ContactSubmission $contactSubmission): RedirectResponse
    {
        $oldValues = $contactSubmission->only(['status']);
        $data = $request->validated();

        $contactSubmission->update($data);

        $this->auditLogger->log($request->user(), 'update', $contactSubmission, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status updated.',
        ]);

        return back();
    }

    public function destroy(ContactSubmission $contactSubmission): RedirectResponse
    {
        $this->authorize('delete', $contactSubmission);

        $this->auditLogger->log(request()->user(), 'delete', $contactSubmission, oldValues: $contactSubmission->only(['name', 'email', 'status']));
        $contactSubmission->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Message deleted.',
        ]);

        return back();
    }
}
