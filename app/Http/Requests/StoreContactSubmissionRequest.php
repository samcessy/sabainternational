<?php

namespace App\Http\Requests;

use App\Enums\ContactSubject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Fields per saba.md §23.1. The honeypot field ("website") is intentionally
 * not validated here — see App\Http\Controllers\Concerns\DetectsHoneypot.
 */
class StoreContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', new Enum(ContactSubject::class)],
            'message' => ['required', 'string', 'min:20'],
            'consent' => ['accepted'],
        ];
    }
}
