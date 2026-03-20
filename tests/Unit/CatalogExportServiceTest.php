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
        $this->assertStringContainsString('The Matrix', $lines[1]);
        $this->assertStringContainsString('Inception', $lines[2]);
    }

    public function test_it_filters_csv_by_batch_id_when_provided(): void
    {
        // 1. Arrange
        $batch1 = Batch::create([
            'status' => 'completed',
            'original_filename' => 'batch1.txt',
        ]);
        $batch2 = Batch::create([
            'status' => 'completed',
            'original_filename' => 'batch2.txt',
        ]);

        MediaItem::create([
            'batch_id' => $batch1->id,
            'raw_data' => 'item 1',
            'product_name' => 'Product 1',
        ]);

        MediaItem::create([
            'batch_id' => $batch2->id,
            'raw_data' => 'item 2',
            'product_name' => 'Product 2',
        ]);

        $service = new CatalogExportService();

        // 2. Act
        $csv = $service->generateCsv($batch1->id);

        // 3. Assert
        $cleanCsv = ltrim($csv, "\xEF\xBB\xBF");
        $lines = explode("\n", trim($cleanCsv));

        $this->assertCount(2, $lines); // Header + 1 data row
        $this->assertStringContainsString('Product 1', $lines[1]);
        $this->assertStringNotContainsString('Product 2', $csv);
    }
}
