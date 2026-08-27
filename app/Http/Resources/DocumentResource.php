<?php

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Document
 */
class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'document_type' => $this->document_type->value,
            'document_type_label' => $this->document_type->label(),
            'year' => $this->year,
            'summary' => $this->summary,
            'file_url' => $this->file->url(),
            'cover_image_url' => $this->coverImage?->url(),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
