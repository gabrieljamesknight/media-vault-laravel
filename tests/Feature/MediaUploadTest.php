<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\MediaItem;
use App\Services\MediaUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_text_is_split_into_multiple_media_items()
    {
        $service = app(MediaUploadService::class);
        $rawText = "The Matrix Blu-ray Good\nInception DVD Like New";

        $batch = $service->processUpload($rawText, null);

        $this->assertEquals(2, MediaItem::where('batch_id', $batch->id)->count());
        $this->assertDatabaseHas('media_items', ['raw_data' => 'The Matrix Blu-ray Good']);
        $this->assertDatabaseHas('media_items', ['raw_data' => 'Inception DVD Like New']);
    }

    public function test_raw_text_with_crlf_is_split_into_multiple_media_items()
    {
        $service = app(MediaUploadService::class);
        $rawText = "The Matrix Blu-ray Good\r\nInception DVD Like New";

        $batch = $service->processUpload($rawText, null);

        $this->assertEquals(2, MediaItem::where('batch_id', $batch->id)->count());
    }

    public function test_empty_lines_are_filtered_out()
    {
        $service = app(MediaUploadService::class);
        $rawText = "The Matrix Blu-ray Good\n\nInception DVD Like New\n";

        $batch = $service->processUpload($rawText, null);

        $this->assertEquals(2, MediaItem::where('batch_id', $batch->id)->count());
    }

    public function test_user_reported_case_with_newline()
    {
        $service = app(MediaUploadService::class);
        $rawText = "the matrix bluray good condition \n inception dvd disc only";

        $batch = $service->processUpload($rawText, null);

        $this->assertEquals(2, MediaItem::where('batch_id', $batch->id)->count());
    }

    public function test_user_reported_case_with_literal_newline_string()
    {
        $service = app(MediaUploadService::class);
        $rawText = 'the matrix bluray good condition \n inception dvd disc only';

        $batch = $service->processUpload($rawText, null);

        // This will likely fail if the logic doesn't handle literal \n
        $this->assertEquals(2, MediaItem::where('batch_id', $batch->id)->count());
    }
}
