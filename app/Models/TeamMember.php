<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

/**
 * @property ContentStatus $status
 * @property string|null $bio
 */
#[Fillable([
    'name', 'role', 'bio', 'photo_media_id', 'board_member',
    'consent_to_publish', 'display_order', 'status',
])]
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'board_member' => 'boolean',
            'consent_to_publish' => 'boolean',
            'status' => ContentStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    /**
     * Blocks publishing a placeholder bio — closes the exact gap the audit
     * found (docs/audit/current-website-audit.md F-9). See
     * docs/content-model.md §2.3.
     */
    protected static function booted(): void
    {
        static::saving(function (TeamMember $teamMember) {
            if ($teamMember->status === ContentStatus::Published && blank($teamMember->bio)) {
                throw new RuntimeException('A team member cannot be published without a bio.');
            }
        });
    }
}
