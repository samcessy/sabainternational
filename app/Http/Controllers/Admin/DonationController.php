<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DonationStatus;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Services\AuditLogger;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Read-only from the admin's perspective (DonationPolicy has create/update
 * abilities for potential future use, but per its own docblock, status
 * changes are driven by the Stripe webhook, not manual admin edits - so no
 * form is built for them here). "Anonymous" only governs public display
 * (e.g. a future donor wall), not this internal view - the org still needs
 * real donor records for receipts/compliance regardless of that preference.
 */
class DonationController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Donation::class);

        return Inertia::render('admin/donations/Index', [
            'donations' => Donation::query()
                ->with(['supporter', 'program', 'transactions'])
                ->latest()
                ->paginate(20)
                ->through(fn (Donation $donation) => [
                    'id' => $donation->id,
                    'supporter_name' => $donation->supporter->name,
                    'supporter_email' => $donation->supporter->email,
                    'program' => $donation->program?->name,
                    'amount' => number_format($donation->amount_cents / 100, 2),
                    'currency' => $donation->currency,
                    'frequency_label' => $donation->frequency->label(),
                    'status' => $donation->status->value,
                    'status_label' => $donation->status->label(),
                    'anonymous' => $donation->anonymous,
                    'created_at' => $donation->created_at?->toIso8601String(),
                    'transactions' => $donation->transactions
                        ->map(fn (DonationTransaction $transaction): array => [
                            'id' => $transaction->id,
                            'gateway_reference' => $transaction->gateway_reference,
                            'status_label' => $transaction->status->label(),
                            'receipt_sent_at' => $transaction->receipt_sent_at?->toIso8601String(),
                        ])
                        ->values()
                        ->all(),
                ]),
            'totals' => [
                'succeeded_count' => Donation::query()->where('status', DonationStatus::Succeeded)->count(),
                'succeeded_amount' => number_format(
                    Donation::query()->where('status', DonationStatus::Succeeded)->sum('amount_cents') / 100,
                    2
                ),
            ],
        ]);
    }

    public function export(): StreamedResponse
    {
        $this->authorize('export', Donation::class);

        $this->auditLogger->log(request()->user(), 'export-donor-data', new Donation);

        $filename = 'donations-'.now()->format('Y-m-d-His').'.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new RuntimeException('Unable to open output stream for CSV export.');
            }

            fputcsv($handle, ['Donor Name', 'Donor Email', 'Amount', 'Currency', 'Frequency', 'Program', 'Status', 'Anonymous', 'Date']);

            Donation::query()->with(['supporter', 'program'])->orderBy('created_at')->chunk(200, function ($donations) use ($handle) {
                foreach ($donations as $donation) {
                    fputcsv($handle, [
                        $donation->supporter->name,
                        $donation->supporter->email,
                        number_format($donation->amount_cents / 100, 2),
                        $donation->currency,
                        $donation->frequency->label(),
                        $donation->program?->name,
                        $donation->status->label(),
                        $donation->anonymous ? 'Yes' : 'No',
                        $donation->created_at?->toDateString(),
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
