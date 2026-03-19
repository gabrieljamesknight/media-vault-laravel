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
            'product_name' => $structuredData['product_name'] ?? null,
            'artist_or_director' => $structuredData['artist_or_director'] ?? null,
            'media_format' => $structuredData['media_format'] ?? null,
            'genre' => $structuredData['genre'] ?? null,
            'condition' => $structuredData['condition'] ?? null,
        ]);
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
