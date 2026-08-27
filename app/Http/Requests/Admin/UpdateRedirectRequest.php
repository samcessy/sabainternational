<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('redirect')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Redirect $redirect */
        $redirect = $this->route('redirect');

        return [
            'from_path' => ['required', 'string', 'max:255', 'starts_with:/', Rule::unique('redirects', 'from_path')->ignore($redirect->id)],
            'to_path' => ['required', 'string', 'max:255', 'starts_with:/', 'different:from_path'],
            'status_code' => ['required', Rule::in([301, 302, 307, 308])],
        ];
    }
}
