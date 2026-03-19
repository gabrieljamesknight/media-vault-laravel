<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class MediaEnrichmentService
{
    /**
     * The Gemini API key.
     */
    private ?string $apiKey;

    /**
     * The Gemini API model.
     */
    private string $model;

    /**
     * The Gemini API base URL.
     */
    private string $baseUrl;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-3.1-pro-preview');
        $this->baseUrl = config('services.gemini.url', 'https://generativelanguage.googleapis.com/v1beta/models');
    }

    /**
     * Enrich a messy media string using the Gemini API.
     *
     * @param string $rawMediaString
     * @return array<string, mixed>|null
     */
    public function enrich(string $rawMediaString): ?array
    {
        if (!$this->apiKey) {
            Log::error('Gemini API key is not configured.');
            return null;
        }

        $prompt = "You are a data categorizer. Extract the following into JSON: product_name, artist_or_director, media_format, genre, condition. If you cannot determine a value, return null. Return ONLY raw JSON. \n\nInput: {$rawMediaString}";

        try {
            $url = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

            Log::debug('Gemini API Request', ['url' => $url]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generation_config' => [
                    'response_mime_type' => 'application/json',
                    'response_schema' => [
                        'type' => 'object',
                        'properties' => [
                            'product_name' => ['type' => 'string'],
                            'artist_or_director' => ['type' => 'string'],
                            'media_format' => ['type' => 'string'],
                            'genre' => ['type' => 'string'],
                            'condition' => ['type' => 'string'],
                        ],
                    ],
                ]
            ]);


            if ($response->failed()) {
                Log::error('Gemini API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'input' => $rawMediaString
                ]);
                return null;
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                Log::warning('Gemini API returned an empty result', ['input' => $rawMediaString]);
                return null;
            }

            return json_decode($text, true);

        } catch (ConnectionException $e) {
            Log::error('Gemini API timeout or connection error', [
                'message' => $e->getMessage(),
                'input' => $rawMediaString
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected error during Media Enrichment', [
                'message' => $e->getMessage(),
                'input' => $rawMediaString
            ]);
            return null;
        }
    }
}
