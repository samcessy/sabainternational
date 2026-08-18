<?php

namespace App\Http\Requests\Admin;

use App\Models\ImpactMetric;
use Illuminate\Foundation\Http\FormRequest;

class StoreImpactMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ImpactMetric::class) ?? false;
    }

    /**
     * An unselected optional program <Select> submits an empty string, not
     * an absent field - see StoreStoryRequest::prepareForValidation().
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'program_id' => $this->input('program_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:255'],
            'program_id' => ['nullable', 'exists:programs,id'],
        ];
    }
}
