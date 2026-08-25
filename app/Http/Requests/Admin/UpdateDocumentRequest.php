<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('document')) ?? false;
    }

    /**
     * See StoreDocumentRequest::prepareForValidation().
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cover_image_media_id' => $this->input('cover_image_media_id') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', new Enum(DocumentType::class)],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'summary' => ['nullable', 'string'],
            'file_media_id' => ['required', 'exists:media,id'],
            'cover_image_media_id' => ['nullable', 'exists:media,id'],
            'status' => ['required', new Enum(ContentStatus::class)],
        ];
    }
}
