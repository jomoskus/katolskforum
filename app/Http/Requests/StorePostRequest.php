<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Authentication and email verification are enforced by route
     * middleware; every verified member may reply.
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
            'body' => ['required', 'string', 'max:40000'],
        ];
    }

    /**
     * `body` must be overridden ("kropp" from laravel-lang would be
     * anatomical).
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'body' => 'innhold',
        ];
    }
}
