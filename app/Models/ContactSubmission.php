<?php

namespace App\Models;

use App\Enums\ContactSubject;
use App\Enums\SubmissionStatus;
use Database\Factories\ContactSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property ContactSubject $subject
 * @property SubmissionStatus $status
 */
#[Fillable(['name', 'email', 'country', 'organization', 'subject', 'message', 'status'])]
class ContactSubmission extends Model
{
    /** @use HasFactory<ContactSubmissionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'subject' => ContactSubject::class,
            'status' => SubmissionStatus::class,
        ];
    }
}
