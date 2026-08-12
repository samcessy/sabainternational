<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Models\TeamMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TeamMember::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            // TeamMember::booted() throws a RuntimeException (500) for this
            // exact case at save time — catching it here first turns it into
            // a normal field error instead of a crash.
            'bio' => [$this->input('status') === ContentStatus::Published->value ? 'required' : 'nullable', 'string'],
            'board_member' => ['boolean'],
            'consent_to_publish' => ['boolean'],
            'display_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
