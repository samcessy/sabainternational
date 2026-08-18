<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('team_member')) ?? false;
    }

    /**
     * See StoreTeamMemberRequest::prepareForValidation().
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'photo_media_id' => $this->input('photo_media_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            // See StoreTeamMemberRequest::rules() — turns TeamMember's
            // publish-guard RuntimeException into a normal field error.
            'bio' => [$this->input('status') === ContentStatus::Published->value ? 'required' : 'nullable', 'string'],
            'photo_media_id' => ['nullable', 'exists:media,id'],
            'board_member' => ['boolean'],
            'consent_to_publish' => ['boolean'],
            'display_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
