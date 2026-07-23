<?php

namespace App\Services;

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
}