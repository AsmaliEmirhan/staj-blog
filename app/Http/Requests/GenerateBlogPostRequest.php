<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'keywords' => [
                'required',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'keywords.required' => 'Blog yazısı üretmek için anahtar kelimeler gereklidir.',
            'keywords.string' => 'Anahtar kelimeler metin olmalıdır.',
            'keywords.max' => 'Anahtar kelimeler en fazla 500 karakter olabilir.',
        ];
    }
}
