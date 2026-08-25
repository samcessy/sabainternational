<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Event::class) ?? false;
    }

    /**
     * An unselected optional <MediaPicker> submits an empty string, not an
     * absent field - see StoreStoryRequest::prepareForValidation().
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:events,slug'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'featured_image_media_id' => ['nullable', 'exists:media,id'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
