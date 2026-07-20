<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Ziyaretçilerin kayıt isteği göndermesine izin verir.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Kayıt formundan gelen verileri doğrular.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    /**
     * Doğrulamadan önce kullanıcı adı ve e-postayı standartlaştırır.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'username' => strtolower(
                trim((string) $this->input('username'))
            ),
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
            'name.required' => 'Ad soyad alanı zorunludur.',
            'name.min' => 'Ad soyad en az 2 karakter olmalıdır.',
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.min' => 'Kullanıcı adı en az 3 karakter olmalıdır.',
            'username.max' => 'Kullanıcı adı en fazla 50 karakter olabilir.',
            'username.regex' => 'Kullanıcı adı yalnızca küçük harf, rakam ve alt çizgi içerebilir.',
            'username.unique' => 'Bu kullanıcı adı daha önce alınmış.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresiyle daha önce kayıt olunmuş.',
            'password.required' => 'Parola alanı zorunludur.',
            'password.confirmed' => 'Parola tekrarı eşleşmiyor.',
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
            'name' => 'ad soyad',
            'username' => 'kullanıcı adı',
            'email' => 'e-posta adresi',
            'password' => 'parola',
        ];
    }
}
