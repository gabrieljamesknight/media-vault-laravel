<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Collection;

class CatalogExportService
{
    /**
     * Generate a CSV string of successfully processed media items.
     *
     * @param int|null $batchId
     * @return string
     */
    public function generateCsv(?int $batchId = null): string
    {
        $query = MediaItem::whereNotNull('product_name');

        if ($batchId !== null) {
            $query->where('batch_id', $batchId);
        }

        $mediaItems = $query->get();

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('Failed to open temporary stream for CSV generation.');
        }

        // Add UTF-8 BOM for Excel compatibility
        fputs($handle, "\xEF\xBB\xBF");

        // Define Headers
        fputcsv($handle, [
            'Product Name',
            'Artist/Director',
            'Format',
            'Genre',
            'Condition',
            'Raw Input',
        ]);

        /** @var MediaItem $item */
        foreach ($mediaItems as $item) {
            fputcsv($handle, [
                $item->product_name,
                $item->artist_or_director,
                $item->media_format,
                $item->genre,
                $item->condition,
                $item->raw_data,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv !== false ? $csv : '';
    }
}
