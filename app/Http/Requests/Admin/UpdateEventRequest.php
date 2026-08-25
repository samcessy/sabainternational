<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event')) ?? false;
    }

    /**
     * See StoreEventRequest::prepareForValidation().
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
        /** @var Event $event */
        $event = $this->route('event');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('events', 'slug')->ignore($event->id)],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'featured_image_media_id' => ['nullable', 'exists:media,id'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
