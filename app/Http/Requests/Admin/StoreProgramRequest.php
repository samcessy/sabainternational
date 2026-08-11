<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ProgramRelationshipType;
use App\Enums\SensitiveContentClassification;
use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Program::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:programs,slug'],
            'category' => ['required', new Enum(ProgramCategory::class)],
            'relationship_type' => ['required', new Enum(ProgramRelationshipType::class)],
            'external_url' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'location' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'overview' => ['nullable', 'string'],
            'what_happens_here' => ['nullable', 'string'],
            'sensitive_content_classification' => ['required', new Enum(SensitiveContentClassification::class)],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
