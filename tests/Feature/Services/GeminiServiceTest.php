<?php

namespace Tests\Feature\Services;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'test-model',
            'services.gemini.max_output_tokens' => 2000,
            'services.gemini.timeout' => 30,
        ]);
    }

    public function test_generate_text_returns_gemini_response(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Test blog metni'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = app(GeminiService::class)
            ->generateText('Test istemi');

        $this->assertSame('Test blog metni', $result);
    }

    public function test_generate_text_rejects_empty_prompt(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gemini istemi boş olamaz.');

        app(GeminiService::class)->generateText('   ');
    }

    public function test_generate_text_rejects_invalid_gemini_response(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [],
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Gemini geçerli bir metin yanıtı üretmedi.'
        );

        app(GeminiService::class)->generateText('Test istemi');
    }
}