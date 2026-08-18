<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreImpactMetricValueRequest;
use App\Models\ImpactMetric;
use App\Models\ImpactMetricValue;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * Nested under ImpactMetric rather than its own top-level admin resource -
 * there's no ImpactMetricValuePolicy, mutating a value is authorized as
 * "update" on the metric it belongs to, matching how the data is actually
 * managed (from the metric's edit page, not standalone).
 */
class ImpactMetricValueController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function store(StoreImpactMetricValueRequest $request, ImpactMetric $impactMetric): RedirectResponse
    {
        $data = [...$request->validated(), 'last_updated_at' => now()];

        $value = $impactMetric->values()->create($data);

        $this->auditLogger->log($request->user(), 'create', $value, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Value recorded.',
        ]);

        return back();
    }

    public function destroy(ImpactMetric $impactMetric, ImpactMetricValue $value): RedirectResponse
    {
        $this->authorize('update', $impactMetric);

        $this->auditLogger->log(request()->user(), 'delete', $value, oldValues: $value->only(['value', 'time_period', 'verification_status']));
        $value->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Value deleted.',
        ]);

        return back();
    }
}
