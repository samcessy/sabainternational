<?php

namespace App\Http\Requests\Admin;

use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreImpactMetricValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('impact_metric')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:0'],
            'time_period' => ['required', 'string', 'max:255'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'verification_status' => ['required', new Enum(VerificationStatus::class)],
        ];
    }
}
