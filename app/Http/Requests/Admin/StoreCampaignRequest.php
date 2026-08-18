<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Campaign::class) ?? false;
    }

    /**
     * See StoreStoryRequest::prepareForValidation() - an unselected
     * <MediaPicker> submits an empty string, not an absent field.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'featured_image_media_id' => $this->input('featured_image_media_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:campaigns,slug'],
            'description' => ['nullable', 'string'],
            'goal_amount' => ['nullable', 'numeric', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'featured_image_media_id' => ['nullable', 'exists:media,id'],
            'impact_statement' => ['nullable', 'string'],
            'suggested_amounts' => ['nullable', 'string'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
