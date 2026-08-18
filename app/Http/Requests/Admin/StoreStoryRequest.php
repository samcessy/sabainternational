<?php

namespace App\Http\Requests\Admin;

use App\Enums\ApprovalStage;
use App\Enums\ConsentStatus;
use App\Enums\ContentStatus;
use App\Enums\ImageConsentStatus;
use App\Enums\SensitiveContentClassification;
use App\Enums\StoryType;
use App\Models\Story;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Story::class) ?? false;
    }

    /**
     * An unselected optional <Select> submits an empty string, not an
     * absent field - "nullable" doesn't treat "" as null, so exists:
     * would otherwise fail on a deliberately-blank program_id/image_consent.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'program_id' => $this->input('program_id') ?: null,
            'image_consent' => $this->input('image_consent') ?: null,
            'featured_image_media_id' => $this->input('featured_image_media_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:stories,slug'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'featured_image_media_id' => ['nullable', 'exists:media,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'story_type' => ['required', new Enum(StoryType::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'consent_status' => ['required', new Enum(ConsentStatus::class)],
            'image_consent' => ['nullable', new Enum(ImageConsentStatus::class)],
            'guardian_consent' => ['nullable', 'string', 'max:255'],
            'anonymity_requested' => ['boolean'],
            'sensitive_content_classification' => ['required', new Enum(SensitiveContentClassification::class)],
            'approval_stage' => ['required', new Enum(ApprovalStage::class)],
            'attribution' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(ContentStatus::class)],
            'featured' => ['boolean'],
        ];
    }
}
