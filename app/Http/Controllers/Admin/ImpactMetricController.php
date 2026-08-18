<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreImpactMetricRequest;
use App\Http\Requests\Admin\UpdateImpactMetricRequest;
use App\Models\ImpactMetric;
use App\Models\ImpactMetricValue;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ImpactMetricController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', ImpactMetric::class);

        return Inertia::render('admin/impact-metrics/Index', [
            'impactMetrics' => ImpactMetric::query()
                ->with(['program', 'values'])
                ->orderBy('name')
                ->paginate(20)
                ->through(fn (ImpactMetric $metric) => [
                    'id' => $metric->id,
                    'name' => $metric->name,
                    'unit' => $metric->unit,
                    'program' => $metric->program?->name,
                    'latest_verified_value' => $metric->latestVerifiedValue()?->value,
                    'value_count' => $metric->values->count(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ImpactMetric::class);

        return Inertia::render('admin/impact-metrics/Create', [
            'programOptions' => Program::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreImpactMetricRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $metric = ImpactMetric::create($data);

        $this->auditLogger->log($request->user(), 'create', $metric, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$metric->name}\" was created.",
        ]);

        return to_route('admin.impact-metrics.index');
    }

    public function edit(ImpactMetric $impactMetric): Response
    {
        $this->authorize('update', $impactMetric);

        return Inertia::render('admin/impact-metrics/Edit', [
            'impactMetric' => $impactMetric->only(['id', 'name', 'unit', 'program_id']),
            'programOptions' => Program::query()->orderBy('name')->get(['id', 'name']),
            'values' => $impactMetric->values()
                ->latest('last_updated_at')
                ->get()
                ->map(fn (ImpactMetricValue $value) => [
                    'id' => $value->id,
                    'value' => $value->value,
                    'time_period' => $value->time_period,
                    'data_source' => $value->data_source,
                    'verification_status' => $value->verification_status->value,
                    'verification_status_label' => $value->verification_status->label(),
                    'last_updated_at' => $value->last_updated_at?->toIso8601String(),
                ]),
            'verificationStatusOptions' => VerificationStatus::options(),
        ]);
    }

    public function update(UpdateImpactMetricRequest $request, ImpactMetric $impactMetric): RedirectResponse
    {
        $oldValues = $impactMetric->only(array_keys($request->validated()));
        $data = $request->validated();

        $impactMetric->update($data);

        $this->auditLogger->log($request->user(), 'update', $impactMetric, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$impactMetric->name}\" was updated.",
        ]);

        return to_route('admin.impact-metrics.index');
    }

    public function destroy(ImpactMetric $impactMetric): RedirectResponse
    {
        $this->authorize('delete', $impactMetric);

        $name = $impactMetric->name;
        $this->auditLogger->log(request()->user(), 'delete', $impactMetric, oldValues: $impactMetric->only(['name', 'unit']));
        $impactMetric->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$name}\" was deleted.",
        ]);

        return to_route('admin.impact-metrics.index');
    }
}
