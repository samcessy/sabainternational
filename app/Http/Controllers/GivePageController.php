<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Program;
use Inertia\Inertia;
use Inertia\Response;

class GivePageController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Give', [
            // Publishable key only — never the secret key. Empty in any
            // environment without real Stripe credentials configured; the
            // page renders a clear "not yet configured" state rather than
            // silently failing when that's the case.
            'stripeKey' => config('services.stripe.key'),
            'programs' => Program::query()
                ->where('status', ContentStatus::Published)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Program $program) => [
                    'id' => $program->id,
                    'name' => $program->name,
                ]),
        ]);
    }

    public function thankYou(): Response
    {
        return Inertia::render('GiveThankYou');
    }
}
