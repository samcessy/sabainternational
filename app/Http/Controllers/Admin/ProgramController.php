<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ProgramRelationshipType;
use App\Enums\SensitiveContentClassification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProgramRequest;
use App\Http\Requests\Admin\UpdateProgramRequest;
use App\Models\Program;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Program::class);

        return Inertia::render('admin/programs/Index', [
            'programs' => Program::query()
                ->orderBy('name')
                ->paginate(20)
                ->through(fn (Program $program) => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'slug' => $program->slug,
                    'category_label' => $program->category->label(),
                    'status' => $program->status->value,
                    'status_label' => $program->status->label(),
                    'updated_at' => $program->updated_at?->toIso8601String(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Program::class);

        return Inertia::render('admin/programs/Create', [
            'categoryOptions' => ProgramCategory::options(),
            'relationshipTypeOptions' => ProgramRelationshipType::options(),
            'sensitiveContentOptions' => SensitiveContentClassification::options(),
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function store(StoreProgramRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === ContentStatus::Published->value) {
            $data['published_at'] = now();
        }

        $program = Program::create($data);

        $this->auditLogger->log($request->user(), 'create', $program, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$program->name}\" was created.",
        ]);

        return to_route('admin.programs.index');
    }

    public function edit(Program $program): Response
    {
        $this->authorize('update', $program);

        return Inertia::render('admin/programs/Edit', [
            'program' => $program->only([
                'id', 'name', 'legal_name', 'slug', 'category', 'relationship_type',
                'external_url', 'founded_year', 'location', 'short_description',
                'overview', 'what_happens_here', 'sensitive_content_classification',
                'seo_title', 'seo_description', 'og_image', 'status',
            ]),
            'categoryOptions' => ProgramCategory::options(),
            'relationshipTypeOptions' => ProgramRelationshipType::options(),
            'sensitiveContentOptions' => SensitiveContentClassification::options(),
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function update(UpdateProgramRequest $request, Program $program): RedirectResponse
    {
        $oldValues = $program->only(array_keys($request->validated()));
        $data = $request->validated();

        if ($data['status'] === ContentStatus::Published->value && $program->published_at === null) {
            $data['published_at'] = now();
        }

        $program->update($data);

        $this->auditLogger->log($request->user(), 'update', $program, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$program->name}\" was updated.",
        ]);

        return to_route('admin.programs.index');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);

        $name = $program->name;
        $this->auditLogger->log(request()->user(), 'delete', $program, oldValues: $program->only(['name', 'slug', 'status']));
        $program->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$name}\" was deleted.",
        ]);

        return to_route('admin.programs.index');
    }
}
