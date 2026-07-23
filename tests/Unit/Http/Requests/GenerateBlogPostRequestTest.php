<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\GenerateBlogPostRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GenerateBlogPostRequestTest extends TestCase
{
    private GenerateBlogPostRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new GenerateBlogPostRequest;
    }

    public function test_accepts_valid_keywords(): void
    {
        $validator = Validator::make(
            ['keywords' => 'Laravel, PHP, güvenli blog geliştirme'],
            $this->request->rules(),
            $this->request->messages()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_missing_keywords(): void
    {
        $validator = Validator::make(
            [],
            $this->request->rules(),
            $this->request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Blog yazısı üretmek için anahtar kelimeler gereklidir.',
            $validator->errors()->first('keywords')
        );
    }

    public function test_rejects_keywords_longer_than_500_characters(): void
    {
        $validator = Validator::make(
            ['keywords' => str_repeat('a', 501)],
            $this->request->rules(),
            $this->request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Anahtar kelimeler en fazla 500 karakter olabilir.',
            $validator->errors()->first('keywords')
        );
    }
}
