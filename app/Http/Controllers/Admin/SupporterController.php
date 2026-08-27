<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DonationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSupporterRequest;
use App\Models\Donation;
use App\Models\Supporter;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SupporterPolicy's view/update/export abilities existed with no
 * controller behind them at all - donors were only ever visible inline on
 * each Donation row (app/Http/Controllers/Admin/DonationController.php),
 * with no single place to see a supporter's full giving history or fix a
 * typo'd name/email.
 */
class SupporterController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Supporter::class);

        return Inertia::render('admin/supporters/Index', [
            'supporters' => Supporter::query()
                ->withCount('donations')
                ->withSum(['donations as succeeded_amount_cents' => fn ($query) => $query->where('status', DonationStatus::Succeeded)], 'amount_cents')
                ->orderBy('name')
                ->paginate(20)
                ->through(fn (Supporter $supporter) => [
                    'id' => $supporter->id,
                    'name' => $supporter->name,
                    'email' => $supporter->email,
                    'donations_count' => $supporter->donations_count,
                    'total_donated' => number_format(($supporter->succeeded_amount_cents ?? 0) / 100, 2),
                ]),
        ]);
    }

    public function show(Supporter $supporter): Response
    {
        $this->authorize('view', $supporter);

        return Inertia::render('admin/supporters/Show', [
            'supporter' => [
                'id' => $supporter->id,
                'name' => $supporter->name,
                'email' => $supporter->email,
                'created_at' => $supporter->created_at?->toIso8601String(),
            ],
            'donations' => $supporter->donations()
                ->with('program')
                ->latest()
                ->get()
                ->map(fn (Donation $donation) => [
                    'id' => $donation->id,
                    'amount' => number_format($donation->amount_cents / 100, 2),
                    'currency' => $donation->currency,
                    'frequency_label' => $donation->frequency->label(),
                    'program' => $donation->program?->name,
                    'status' => $donation->status->value,
                    'status_label' => $donation->status->label(),
                    'created_at' => $donation->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function edit(Supporter $supporter): Response
    {
        $this->authorize('update', $supporter);

        return Inertia::render('admin/supporters/Edit', [
            'supporter' => $supporter->only(['id', 'name', 'email']),
        ]);
    }

    public function update(UpdateSupporterRequest $request, Supporter $supporter): RedirectResponse
    {
        $oldValues = $supporter->only(array_keys($request->validated()));
        $data = $request->validated();

        $supporter->update($data);

        $this->auditLogger->log($request->user(), 'update', $supporter, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$supporter->name}\" was updated.",
        ]);

        return to_route('admin.supporters.show', $supporter);
    }

    public function export(): StreamedResponse
    {
        $this->authorize('export', Supporter::class);

        $this->auditLogger->log(request()->user(), 'export-donor-data', new Supporter);

        $filename = 'supporters-'.now()->format('Y-m-d-His').'.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open output stream for CSV export.');
            }

            fputcsv($handle, ['Name', 'Email', 'Total Donated', 'Donation Count', 'Supporter Since']);

            Supporter::query()
                ->withCount('donations')
                ->withSum(['donations as succeeded_amount_cents' => fn ($query) => $query->where('status', DonationStatus::Succeeded)], 'amount_cents')
                ->orderBy('created_at')
                ->chunk(200, function ($supporters) use ($handle) {
                    foreach ($supporters as $supporter) {
                        fputcsv($handle, [
                            $supporter->name,
                            $supporter->email,
                            number_format(($supporter->succeeded_amount_cents ?? 0) / 100, 2),
                            $supporter->donations_count,
                            $supporter->created_at?->toDateString(),
                        ]);
                    }
                });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
