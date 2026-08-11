<?php

namespace App\Http\Requests;

use App\Enums\ImageConsentStatus;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * alt_text and consent_status are required at upload, not deferred to some
 * later publish step — Media has no lifecycle status of its own (unlike
 * Story/TeamMember), so upload time is the only point to enforce this.
 * See saba.md §14.1, docs/architecture/media-architecture.md §7.
 */
class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Extension whitelist, not blacklist — mimes: validates the
            // actual file content against these types, not just the
            // client-supplied filename/extension.
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'alt_text' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'photographer' => ['nullable', 'string', 'max:255'],
            'copyright_license' => ['nullable', 'string', 'max:255'],
            'consent_status' => ['required', new Enum(ImageConsentStatus::class)],
            'program_id' => ['nullable', 'exists:programs,id'],
            'story_id' => ['nullable', 'exists:stories,id'],
            'focal_point_x' => ['nullable', 'numeric', 'between:0,1'],
            'focal_point_y' => ['nullable', 'numeric', 'between:0,1'],
        ];
    }
}
