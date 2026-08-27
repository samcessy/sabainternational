<?php

namespace App\Http\Controllers;

use App\Models\ImpactMetric;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The homepage's "Areas of Impact" section promises verified impact
 * metrics "as they're finalized" rather than fabricating numbers -
 * saba.md §6.3, docs/content-model.md §2.8. This controller is what
 * fulfills that promise: only metrics with a verified value are shown, so
 * an unverified/estimated one never renders as a hard public number.
 */
class HomeController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Home', [
            'impactMetrics' => ImpactMetric::query()
                ->with('program')
                ->get()
                ->map(fn (ImpactMetric $metric) => [
                    'id' => $metric->id,
                    'name' => $metric->name,
                    'unit' => $metric->unit,
                    'program' => $metric->program?->name,
                    'value' => $metric->latestVerifiedValue()?->value,
                ])
                ->filter(fn (array $metric) => $metric['value'] !== null)
                ->values(),
        ]);
    }
}
