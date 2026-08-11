<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Only one_time and monthly in V1 — see docs/product-requirements.md §3.
 * Currency is fixed to USD (not a validated input) per the same doc.
 */
class StoreDonationRequest extends FormRequest
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
            // $1.00 minimum, $50,000.00 sanity ceiling against typos/abuse.
            'amount_cents' => ['required', 'integer', 'min:100', 'max:5000000'],
            // Deliberately 'in:', not the full DonationFrequency enum rule —
            // quarterly/annual are modeled in the schema but not offered
            // as choices until V2 (docs/product-requirements.md §3).
            'frequency' => ['required', 'string', 'in:one_time,monthly'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'anonymous' => ['boolean'],
        ];
    }
}
