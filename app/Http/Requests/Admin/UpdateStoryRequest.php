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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('story')) ?? false;
    }

    /**
     * See StoreStoryRequest::prepareForValidation().
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'program_id' => $this->input('program_id') ?: null,
            'image_consent' => $this->input('image_consent') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Story $story */
        $story = $this->route('story');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('stories', 'slug')->ignore($story->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
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
