<?php

namespace App\Http\Resources;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Page
 */
class PageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'og_image' => $this->og_image,
            ],
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
