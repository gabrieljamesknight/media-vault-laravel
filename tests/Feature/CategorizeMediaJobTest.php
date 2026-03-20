<?php

namespace Tests\Feature;

use App\Jobs\CategorizeMediaJob;
use App\Models\MediaItem;
use App\Models\Batch;
use App\Services\MediaEnrichmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Mockery;

class CategorizeMediaJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_fields_to_title_case_when_processing()
    {
        // 1. Arrange
        $batch = Batch::create([
            'status' => 'pending',
            'original_filename' => 'test_upload.txt',
        ]);
        $mediaItem = MediaItem::create([
            'batch_id' => $batch->id,
            'raw_data' => 'the matrix reloaded dvd used',
        ]);

        $mockEnrichmentService = Mockery::mock(MediaEnrichmentService::class);
        $mockEnrichmentService->shouldReceive('enrich')
            ->once()
            ->with('the matrix reloaded dvd used')
            ->andReturn([
                'product_name' => 'the matrix reloaded',
                'artist_or_director' => 'the wachowskis',
                'media_format' => 'dvd',
                'genre' => 'sci-fi',
                'condition' => 'used',
            ]);

        // 2. Act
        $job = new CategorizeMediaJob($mediaItem);
        $job->handle($mockEnrichmentService);

        // 3. Assert
        $mediaItem->refresh();
        $this->assertEquals('The Matrix Reloaded', $mediaItem->product_name);
        $this->assertEquals('The Wachowskis', $mediaItem->artist_or_director);
        $this->assertEquals('Dvd', $mediaItem->media_format);
        $this->assertEquals('Sci-Fi', $mediaItem->genre);
        $this->assertEquals('Used', $mediaItem->condition);
    }
}
