<?php

namespace App\Http\Requests\Admin;

use App\Models\StoryTag;
use Illuminate\Foundation\Http\FormRequest;

class StoreStoryTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StoryTag::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:story_tags,slug'],
        ];
    }
}
