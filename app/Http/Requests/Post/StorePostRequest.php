<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Kullanıcının yeni yazı oluşturma yetkisini PostPolicy üzerinden kontrol eder.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) === true;
    }

    /**
     * Form verilerini doğrulamadan önce standartlaştırır.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->trimString($this->input('title')),
            'excerpt' => $this->nullableTrimmedString(
                $this->input('excerpt')
            ),
            'content' => $this->trimString($this->input('content')),
            'category_id' => $this->filled('category_id')
                ? $this->input('category_id')
                : null,
            'status' => $this->input('status', Post::STATUS_DRAFT),
            'published_at' => $this->filled('published_at')
                ? $this->input('published_at')
                : null,
        ]);
    }

    /**
     * Yeni blog yazısı için doğrulama kurallarını döndürür.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'title' => [
                'required',
                'string',
                'min:3',
                'max:200',
            ],
            'excerpt' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'content' => [
                'required',
                'string',
                'min:50',
            ],
            'status' => [
                'required',
                'string',
                Rule::in([
                    Post::STATUS_DRAFT,
                    Post::STATUS_PUBLISHED,
                ]),
            ],
            'published_at' => [
                Rule::requiredIf(
                    fn (): bool => $this->input('status')
                        === Post::STATUS_PUBLISHED
                ),
                'nullable',
                'date',
            ],
            'tag_ids' => [
                'sometimes',
                'array',
                'max:10',
            ],
            'tag_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('tags', 'id'),
            ],
        ];
    }

    /**
     * Doğrulama hataları için Türkçe alan adlarını döndürür.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'title' => 'başlık',
            'excerpt' => 'özet',
            'content' => 'yazı içeriği',
            'status' => 'yayın durumu',
            'published_at' => 'yayın tarihi',
            'tag_ids' => 'etiketler',
            'tag_ids.*' => 'etiket',
        ];
    }

    /**
     * Projeye özel Türkçe doğrulama mesajlarını döndürür.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'published_at.required' => 'Yayınlanacak yazılar için yayın tarihi zorunludur.',
            'status.in' => 'Yayın durumu yalnızca taslak veya yayında olabilir.',
            'tag_ids.max' => 'Bir yazıya en fazla 10 etiket ekleyebilirsiniz.',
            'tag_ids.*.distinct' => 'Aynı etiket bir yazıya birden fazla kez eklenemez.',
            'category_id.exists' => 'Seçilen kategori bulunamadı.',
            'tag_ids.*.exists' => 'Seçilen etiketlerden biri bulunamadı.',
        ];
    }

    private function trimString(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    private function nullableTrimmedString(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
