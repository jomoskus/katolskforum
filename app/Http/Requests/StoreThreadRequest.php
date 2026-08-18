<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ThreadKind;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreThreadRequest extends FormRequest
{
    /**
     * Authentication and email verification are enforced by route
     * middleware; every verified member may start a thread.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'kind' => ['required', Rule::enum(ThreadKind::class)],
            'body' => ['required', 'string', 'max:40000'],
            'url' => ['nullable', 'required_if:kind,link', 'url:http,https', 'max:2048'],
        ];
    }

    /**
     * A URL only belongs to link threads; clear it for everything else so
     * switching kind never leaves a stale URL behind.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('kind') !== ThreadKind::Link->value) {
            $this->merge(['url' => null]);
        }
    }

    /**
     * Field names as members see them in error messages. `title` is
     * omitted because laravel-lang already translates it; `body` must be
     * overridden ("kropp" would be anatomical).
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kind' => 'type',
            'body' => 'innhold',
            'url' => 'lenke',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required_if' => 'Du må oppgi en lenke når tråden er av typen «Lenke».',
            'url.url' => 'Lenken må være en gyldig nettadresse som starter med http:// eller https://.',
        ];
    }
}
