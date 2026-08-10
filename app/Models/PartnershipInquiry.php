<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Database\Factories\PartnershipInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_name', 'contact_name', 'email', 'details', 'status'])]
class PartnershipInquiry extends Model
{
    /** @use HasFactory<PartnershipInquiryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
        ];
    }
}
