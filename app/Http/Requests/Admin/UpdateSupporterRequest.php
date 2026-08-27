<?php

namespace App\Http\Requests\Admin;

use App\Models\Supporter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Name/email only - a correction flow (a donor's name was misspelled, an
 * email needs updating for correspondence), not a preferences editor.
 * communication_preferences has no defined shape anywhere in this codebase
 * (no producer or consumer sets it), so building a form for it now would
 * mean inventing keys nobody asked for rather than editing something real.
 */
class UpdateSupporterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('supporter')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Supporter $supporter */
        $supporter = $this->route('supporter');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('supporters', 'email')->ignore($supporter->id)],
        ];
    }
}
