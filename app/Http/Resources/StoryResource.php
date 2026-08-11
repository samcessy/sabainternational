<?php

namespace App\Http\Resources;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Story
 */
class StoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'story_type' => $this->story_type->value,
            'location' => $this->location,
            'featured' => $this->featured,
            'program' => $this->whenLoaded('program', fn () => [
                'name' => $this->program->name,
                'slug' => $this->program->slug,
            ]),
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'og_image' => $this->og_image,
            ],
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
