<?php

namespace App\Http\Requests\Admin;

use App\Enums\ImageConsentStatus;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Covers the metadata StoreMediaRequest captures at upload time
 * (app/Http/Controllers/Admin/MediaController.php's docblock flagged
 * editing this after the fact as "deferred - not built yet"). Does not
 * cover replacing the underlying file, program_id/story_id reassignment,
 * or focal point - a metadata correction, not a re-upload or re-linking
 * flow.
 */
class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('media')) ?? false;
    }

    /**
     * Same reasoning as StoreMediaRequest — an unfilled consent_status
     * submits "" not an absent field, which the Enum rule would otherwise
     * reject.
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
        /** @var Media $media */
        $media = $this->route('media');
        $isImage = $media->isImage();

        return [
            'alt_text' => [$isImage ? 'required' : 'nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'photographer' => ['nullable', 'string', 'max:255'],
            'copyright_license' => ['nullable', 'string', 'max:255'],
            'consent_status' => [$isImage ? 'required' : 'nullable', new Enum(ImageConsentStatus::class)],
        ];
    }
}
