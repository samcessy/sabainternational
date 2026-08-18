<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('campaign')) ?? false;
    }

    /**
     * See StoreCampaignRequest::prepareForValidation().
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
        /** @var Campaign $campaign */
        $campaign = $this->route('campaign');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('campaigns', 'slug')->ignore($campaign->id)],
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
