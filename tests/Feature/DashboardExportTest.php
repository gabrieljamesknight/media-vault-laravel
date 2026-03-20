<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_export_csv_via_dashboard_route()
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
}
