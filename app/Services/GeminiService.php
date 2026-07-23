<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class GeminiService
{
    private readonly string $apiKey;
    private readonly string $model;
    private readonly int $maxOutputTokens;
    private readonly int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key');
        $this->model = (string) config('services.gemini.model');
        $this->maxOutputTokens = (int) config('services.gemini.max_output_tokens');
        $this->timeout = (int) config('services.gemini.timeout');

        if ($this->apiKey === '') {
            throw new RuntimeException('Gemini API anahtarı yapılandırılmamış.');
        }
    }

    public function generateText(string $prompt): string
    {
        $prompt = trim($prompt);

        if ($prompt === '') {
            throw new InvalidArgumentException('Gemini istemi boş olamaz.');
        }

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'x-goog-api-key' => $this->apiKey,
            ])
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => $this->maxOutputTokens,
                        'thinkingConfig' => [
                            'thinkingLevel' => 'minimal',
                        ],
                    ],
                ]
            );

        $response->throw();

        $text = data_get(
            $response->json(),
            'candidates.0.content.parts.0.text'
        );

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini geçerli bir metin yanıtı üretmedi.');
        }

        return trim($text);
    }
}