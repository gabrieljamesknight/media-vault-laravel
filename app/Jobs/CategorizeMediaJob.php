<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\MediaItem;
use App\Services\MediaEnrichmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CategorizeMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The MediaItem instance.
     */
    protected MediaItem $mediaItem;

    /**
     * Create a new job instance.
     */
    public function __construct(MediaItem $mediaItem)
    {
        $this->mediaItem = $mediaItem;
    }

    /**
     * Execute the job.
     */
    public function handle(MediaEnrichmentService $service): void
    {
        $structuredData = $service->enrich($this->mediaItem->raw_data);

        if ($structuredData === null) {
            throw new \RuntimeException('Failed to enrich media item data via Gemini API.');
        }

        $this->mediaItem->update([
            'product_name' => isset($structuredData['product_name']) ? Str::title((string) $structuredData['product_name']) : null,
            'artist_or_director' => isset($structuredData['artist_or_director']) ? Str::title((string) $structuredData['artist_or_director']) : null,
            'media_format' => isset($structuredData['media_format']) ? Str::title((string) $structuredData['media_format']) : null,
            'genre' => isset($structuredData['genre']) ? Str::title((string) $structuredData['genre']) : null,
            'condition' => isset($structuredData['condition']) ? Str::title((string) $structuredData['condition']) : null,
        ]);

        $batch = $this->mediaItem->batch;

        if ($batch) {
            // Check if all items in the batch have been processed.
            // Items are considered processed if their updated_at timestamp differs from created_at.
            $allProcessed = $batch->mediaItems()
                ->whereColumn('updated_at', '=', 'created_at')
                ->count() === 0;

            if ($allProcessed) {
                $batch->update(['status' => 'completed']);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $batch = $this->mediaItem->batch;

        if ($batch) {
            $batch->update(['status' => 'failed']);
        }

        Log::error('CategorizeMediaJob failed', [
            'media_item_id' => $this->mediaItem->id,
            'batch_id' => $this->mediaItem->batch_id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
