<?php

namespace Tests\Unit;

use App\Services\MediaEnrichmentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MediaEnrichmentServiceTest extends TestCase
{
    /**
     * Test that the enrich method correctly parses a successful Gemini response.
     */
    public function test_enrich_returns_structured_data_on_success(): void
    {
        // Mock the configuration
        Config::set('services.gemini.key', 'test-key');
        Config::set('services.gemini.model', 'gemini-1.5-flash');
        Config::set('services.gemini.url', 'https://generativelanguage.googleapis.com/v1beta/models');

        $mockResponse = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'product_name' => 'The Matrix Reloaded',
                                    'artist_or_director' => 'The Wachowskis',
                                    'media_format' => 'DVD',
                                    'genre' => 'Action/Sci-Fi',
                                    'condition' => 'disc only',
                                ])
                            ]
                        ]
                    ]
                ]
            ]
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($mockResponse, 200),
        ]);

        $service = new MediaEnrichmentService();
        $result = $service->enrich('matrix reloaded dvd disc only sf');

        $this->assertNotNull($result);
        $this->assertEquals('The Matrix Reloaded', $result['product_name']);
        $this->assertEquals('The Wachowskis', $result['artist_or_director']);
        $this->assertEquals('DVD', $result['media_format']);
        $this->assertEquals('Action/Sci-Fi', $result['genre']);
        $this->assertEquals('disc only', $result['condition']);
    }

    /**
     * Test that the enrich method returns null on API failure.
     */
    public function test_enrich_returns_null_on_api_failure(): void
    {
        Config::set('services.gemini.key', 'test-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $service = new MediaEnrichmentService();
        $result = $service->enrich('some random string');

        $this->assertNull($result);
    }

    /**
     * Test that the enrich method returns null when API key is missing.
     */
    public function test_enrich_returns_null_when_api_key_missing(): void
    {
        Config::set('services.gemini.key', null);

        $service = new MediaEnrichmentService();
        $result = $service->enrich('some random string');

        $this->assertNull($result);
    }
}
