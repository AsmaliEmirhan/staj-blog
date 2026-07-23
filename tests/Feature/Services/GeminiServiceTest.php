<?php

namespace Tests\Feature\Services;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    public function test_generate_text_returns_gemini_response(): void
    {
        config()->set('services.gemini.api_key', 'test-api-key');
        config()->set('services.gemini.model', 'test-model');
        config()->set('services.gemini.max_output_tokens', 2000);
        config()->set('services.gemini.timeout', 30);

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
}