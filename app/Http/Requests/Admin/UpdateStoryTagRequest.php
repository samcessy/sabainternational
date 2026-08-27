<?php

namespace App\Http\Requests\Admin;

use App\Models\StoryTag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoryTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('story_tag')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var StoryTag $storyTag */
        $storyTag = $this->route('story_tag');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('story_tags', 'slug')->ignore($storyTag->id)],
        ];
    }
}
