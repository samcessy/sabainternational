<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePartnershipInquiryStatusRequest;
use App\Models\PartnershipInquiry;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PartnershipInquiryController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', PartnershipInquiry::class);

        return Inertia::render('admin/partnership-inquiries/Index', [
            'inquiries' => PartnershipInquiry::query()
                ->latest()
                ->paginate(20)
                ->through(fn (PartnershipInquiry $inquiry) => [
                    'id' => $inquiry->id,
                    'organization_name' => $inquiry->organization_name,
                    'contact_name' => $inquiry->contact_name,
                    'email' => $inquiry->email,
                    'details' => $inquiry->details,
                    'status' => $inquiry->status->value,
                    'status_label' => $inquiry->status->label(),
                    'created_at' => $inquiry->created_at?->toIso8601String(),
                ]),
            'statusOptions' => SubmissionStatus::options(),
        ]);
    }

    public function update(UpdatePartnershipInquiryStatusRequest $request, PartnershipInquiry $partnershipInquiry): RedirectResponse
    {
        $oldValues = $partnershipInquiry->only(['status']);
        $data = $request->validated();

        $partnershipInquiry->update($data);

        $this->auditLogger->log($request->user(), 'update', $partnershipInquiry, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status updated.',
        ]);

        return back();
    }

    public function destroy(PartnershipInquiry $partnershipInquiry): RedirectResponse
    {
        $this->authorize('delete', $partnershipInquiry);

        $this->auditLogger->log(request()->user(), 'delete', $partnershipInquiry, oldValues: $partnershipInquiry->only(['organization_name', 'email', 'status']));
        $partnershipInquiry->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Inquiry deleted.',
        ]);

        return back();
    }
}
