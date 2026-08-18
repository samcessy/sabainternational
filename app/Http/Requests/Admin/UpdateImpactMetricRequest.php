<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImpactMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('impact_metric')) ?? false;
    }

    /**
     * See StoreImpactMetricRequest::prepareForValidation().
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
