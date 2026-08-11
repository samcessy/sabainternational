<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVolunteerApplicationStatusRequest;
use App\Models\VolunteerApplication;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VolunteerApplicationController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', VolunteerApplication::class);

        return Inertia::render('admin/volunteer-applications/Index', [
            'applications' => VolunteerApplication::query()
                ->latest()
                ->paginate(20)
                ->through(fn (VolunteerApplication $application) => [
                    'id' => $application->id,
                    'name' => $application->name,
                    'email' => $application->email,
                    'details' => $application->details,
                    'status' => $application->status->value,
                    'status_label' => $application->status->label(),
                    'created_at' => $application->created_at?->toIso8601String(),
                ]),
            'statusOptions' => SubmissionStatus::options(),
        ]);
    }

    public function update(UpdateVolunteerApplicationStatusRequest $request, VolunteerApplication $volunteerApplication): RedirectResponse
    {
        $oldValues = $volunteerApplication->only(['status']);
        $data = $request->validated();

        $volunteerApplication->update($data);

        $this->auditLogger->log($request->user(), 'update', $volunteerApplication, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status updated.',
        ]);

        return back();
    }

    public function destroy(VolunteerApplication $volunteerApplication): RedirectResponse
    {
        $this->authorize('delete', $volunteerApplication);

        $this->auditLogger->log(request()->user(), 'delete', $volunteerApplication, oldValues: $volunteerApplication->only(['name', 'email', 'status']));
        $volunteerApplication->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Application deleted.',
        ]);

        return back();
    }
}
