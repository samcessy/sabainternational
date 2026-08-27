<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Redirect::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_path' => ['required', 'string', 'max:255', 'starts_with:/', 'unique:redirects,from_path'],
            'to_path' => ['required', 'string', 'max:255', 'starts_with:/', 'different:from_path'],
            'status_code' => ['required', Rule::in([301, 302, 307, 308])],
        ];
    }
}
