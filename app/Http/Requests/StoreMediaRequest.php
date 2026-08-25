<?php

namespace App\Http\Requests;

use App\Enums\ImageConsentStatus;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * alt_text and consent_status are required at upload for images, not
 * deferred to some later publish step — Media has no lifecycle status of
 * its own (unlike Story/TeamMember), so upload time is the only point to
 * enforce this. See saba.md §14.1, docs/architecture/media-architecture.md
 * §7. Neither applies to a non-image file (a PDF has no `<img alt>` to
 * fill and doesn't depict an identifiable person), so both are optional
 * there — required-ness is driven by the actual uploaded file's MIME type,
 * not the client-declared extension.
 */
class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    /**
     * An unfilled consent_status on a non-image upload submits an empty
     * string, not an absent field - "nullable" doesn't treat "" as null,
     * so the Enum rule would otherwise reject a deliberately-blank value
     * on a PDF. See StoreStoryRequest::prepareForValidation() for the same
     * pattern elsewhere.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'consent_status' => $this->input('consent_status') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isImage = str_starts_with($this->file('file')?->getMimeType() ?? '', 'image/');

        return [
            // Extension whitelist, not blacklist — mimes: validates the
            // actual file content against these types, not just the
            // client-supplied filename/extension. pdf added for Documents
            // (saba.md §9's Transparency Center) — GenerateMediaVariants
            // only runs for images; see MediaController::store().
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:20480'],
            'alt_text' => [$isImage ? 'required' : 'nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'photographer' => ['nullable', 'string', 'max:255'],
            'copyright_license' => ['nullable', 'string', 'max:255'],
            'consent_status' => [$isImage ? 'required' : 'nullable', new Enum(ImageConsentStatus::class)],
            'program_id' => ['nullable', 'exists:programs,id'],
            'story_id' => ['nullable', 'exists:stories,id'],
            'focal_point_x' => ['nullable', 'numeric', 'between:0,1'],
            'focal_point_y' => ['nullable', 'numeric', 'between:0,1'],
        ];
    }
}
