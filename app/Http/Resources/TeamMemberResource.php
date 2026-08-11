<?php

namespace App\Http\Resources;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeamMember
 */
class TeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'role' => $this->role,
            'bio' => $this->bio,
            'board_member' => $this->board_member,
            'photo' => $this->whenLoaded('photo', fn () => $this->photo ? [
                'path' => $this->photo->path,
                'alt_text' => $this->photo->alt_text,
            ] : null),
        ];
    }
}
