<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Database\Factories\VolunteerApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property SubmissionStatus $status
 */
#[Fillable(['name', 'email', 'details', 'status'])]
class VolunteerApplication extends Model
{
    /** @use HasFactory<VolunteerApplicationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
        ];
    }
}
