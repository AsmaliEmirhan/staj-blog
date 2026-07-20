<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Ziyaretçilerin giriş isteği göndermesine izin verir.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Giriş formundan gelen verileri doğrular.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Doğrulamadan önce e-posta adresini standartlaştırır.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(
                trim((string) $this->input('email'))
            ),
        ]);
    }

    /**
     * Kullanıcıya gösterilecek Türkçe doğrulama mesajlarını döndürür.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Parola alanı zorunludur.',
            'remember.boolean' => 'Beni hatırla alanı geçerli bir değer olmalıdır.',
        ];
    }

    /**
     * Form alanlarının kullanıcı dostu isimlerini döndürür.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'e-posta adresi',
            'password' => 'parola',
            'remember' => 'beni hatırla',
        ];
    }
}
