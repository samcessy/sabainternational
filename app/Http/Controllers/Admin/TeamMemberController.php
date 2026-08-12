<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Admin\UpdateTeamMemberRequest;
use App\Models\TeamMember;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', TeamMember::class);

        return Inertia::render('admin/team-members/Index', [
            'teamMembers' => TeamMember::query()
                ->orderBy('display_order')
                ->paginate(20)
                ->through(fn (TeamMember $teamMember) => [
                    'id' => $teamMember->id,
                    'name' => $teamMember->name,
                    'role' => $teamMember->role,
                    'board_member' => $teamMember->board_member,
                    'status' => $teamMember->status->value,
                    'status_label' => $teamMember->status->label(),
                    'display_order' => $teamMember->display_order,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', TeamMember::class);

        return Inertia::render('admin/team-members/Create', [
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $teamMember = TeamMember::create($this->normalizeBooleans($request));

        $this->auditLogger->log($request->user(), 'create', $teamMember, newValues: $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$teamMember->name}\" was created.",
        ]);

        return to_route('admin.team-members.index');
    }

    public function edit(TeamMember $teamMember): Response
    {
        $this->authorize('update', $teamMember);

        return Inertia::render('admin/team-members/Edit', [
            'teamMember' => $teamMember->only([
                'id', 'name', 'role', 'bio', 'board_member', 'consent_to_publish', 'display_order', 'status',
            ]),
            'statusOptions' => ContentStatus::options(),
        ]);
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $oldValues = $teamMember->only(array_keys($request->validated()));

        $teamMember->update($this->normalizeBooleans($request));

        $this->auditLogger->log($request->user(), 'update', $teamMember, $oldValues, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$teamMember->name}\" was updated.",
        ]);

        return to_route('admin.team-members.index');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $this->authorize('delete', $teamMember);

        $name = $teamMember->name;
        $this->auditLogger->log(request()->user(), 'delete', $teamMember, oldValues: $teamMember->only(['name', 'role', 'status']));
        $teamMember->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$name}\" was deleted.",
        ]);

        return to_route('admin.team-members.index');
    }

    /**
     * See StoryController::normalizeBooleans() — FormData omits unchecked
     * checkboxes entirely, so ->validated() can't be trusted for these.
     *
     * @return array<string, mixed>
     */
    private function normalizeBooleans(StoreTeamMemberRequest|UpdateTeamMemberRequest $request): array
    {
        return [
            ...$request->validated(),
            'board_member' => $request->boolean('board_member'),
            'consent_to_publish' => $request->boolean('consent_to_publish'),
        ];
    }
}
