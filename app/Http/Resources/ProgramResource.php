<?php

namespace App\Http\Resources;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Program
 */
class ProgramResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'slug' => $this->slug,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'relationship_type' => $this->relationship_type->value,
            'external_url' => $this->external_url,
            'founded_year' => $this->founded_year,
            'location' => $this->location,
            'short_description' => $this->short_description,
            'overview' => $this->overview,
            'what_happens_here' => $this->what_happens_here,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'og_image' => $this->og_image,
            ],
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
