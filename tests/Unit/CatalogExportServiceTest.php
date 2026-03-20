<?php

namespace Tests\Unit;

use App\Models\MediaItem;
use App\Models\Batch;
use App\Services\CatalogExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_csv_string_with_successful_items(): void
    {
        // 1. Arrange
        $batch = Batch::create([
            'status' => 'completed',
            'original_filename' => 'export_test.txt',
        ]);

        MediaItem::create([
            'batch_id' => $batch->id,
            'raw_data' => 'raw matrix',
            'product_name' => 'The Matrix',
            'artist_or_director' => 'The Wachowskis',
            'media_format' => 'DVD',
            'genre' => 'Action',
            'condition' => 'Good',
        ]);

        MediaItem::create([
            'batch_id' => $batch->id,
            'raw_data' => 'raw inception',
            'product_name' => 'Inception',
            'artist_or_director' => 'Christopher Nolan',
            'media_format' => 'Blu-ray',
            'genre' => 'Sci-Fi',
            'condition' => 'Mint',
        ]);

        // This item should be excluded as product_name is null
        MediaItem::create([
            'batch_id' => $batch->id,
            'raw_data' => 'messy item',
            'product_name' => null,
        ]);

        $service = new CatalogExportService();

        // 2. Act
        $csv = $service->generateCsv();

        // 3. Assert
        $this->assertNotEmpty($csv);
        
        // Remove BOM for checking lines
        $cleanCsv = ltrim($csv, "\xEF\xBB\xBF");
        $lines = explode("\n", trim($cleanCsv));

        $this->assertCount(3, $lines); // Header + 2 data rows
        $this->assertStringContainsString('Product Name', $lines[0]);
        $this->assertStringContainsString('Artist/Director', $lines[0]);
        $this->assertStringContainsString('Format', $lines[0]);
        $this->assertStringContainsString('Genre', $lines[0]);
        $this->assertStringContainsString('Condition', $lines[0]);
        $this->assertStringContainsString('Raw Input', $lines[0]);
        $this->assertStringContainsString('The Matrix', $lines[1]);
        $this->assertStringContainsString('Inception', $lines[2]);
        $this->assertStringNotContainsString('messy item', $csv);
    }
}
