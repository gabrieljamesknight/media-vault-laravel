<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_export_full_csv_via_dashboard_route()
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

        // 2. Act
        $response = $this->get(route('dashboard.export'));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=structured_catalog.csv');
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('The Matrix', $content);
        $this->assertStringContainsString('Product Name', $content);
    }

    public function test_it_can_export_batch_specific_csv_via_dashboard_route()
    {
        // 1. Arrange
        $batch1 = Batch::create(['status' => 'completed', 'original_filename' => 'b1.txt']);
        $batch2 = Batch::create(['status' => 'completed', 'original_filename' => 'b2.txt']);

        MediaItem::create([
            'batch_id' => $batch1->id,
            'raw_data' => 'raw 1',
            'product_name' => 'Product 1',
        ]);

        MediaItem::create([
            'batch_id' => $batch2->id,
            'raw_data' => 'raw 2',
            'product_name' => 'Product 2',
        ]);

        // 2. Act
        $response = $this->get(route('dashboard.export.batch', $batch1));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', "attachment; filename=batch_{$batch1->id}_export.csv");
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Product 1', $content);
        $this->assertStringNotContainsString('Product 2', $content);
    }
}
