<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\CategorizeMediaJob;
use App\Models\Batch;
use App\Models\MediaItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MediaUploadService
{
    /**
     * Process the uploaded media data.
     *
     * @param string|null $rawText
     * @param UploadedFile|null $csvFile
     * @return Batch
     */
    public function processUpload(?string $rawText, ?UploadedFile $csvFile): Batch
    {
        $filename = $csvFile ? $csvFile->getClientOriginalName() : 'Raw Text Input';

        return DB::transaction(function () use ($rawText, $csvFile, $filename) {
            $batch = Batch::create([
                'original_filename' => $filename,
                'status' => 'pending',
            ]);

            if ($rawText) {
                $this->processRawText($batch, $rawText);
            }

            if ($csvFile) {
                $this->processCsvFile($batch, $csvFile);
            }

            return $batch;
        });
    }

    /**
     * Process raw text input.
     */
    private function processRawText(Batch $batch, string $rawText): void
    {
        // Normalize literal \n strings to real newlines in case they were entered literally
        $normalizedText = str_replace(['\r\n', '\r', '\n', '\\r\\n', '\\r', '\\n'], "\n", $rawText);

        // Split by any combination of newlines (CRLF, LF, CR)
        $lines = explode("\n", $normalizedText);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Filter out empty lines after trimming
            if ($trimmedLine !== '') {
                $this->createMediaItemAndDispatchJob($batch, $trimmedLine);
            }
        }
    }

    /**
     * Process uploaded CSV file.
     */
    private function processCsvFile(Batch $batch, UploadedFile $csvFile): void
    {
        $handle = fopen($csvFile->getRealPath(), 'r');
        if ($handle !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                // Assuming the first column contains the raw media string
                $line = $data[0] ?? null;
                if ($line && trim($line) !== '') {
                    $this->createMediaItemAndDispatchJob($batch, trim($line));
                }
            }
            fclose($handle);
        }
    }

    /**
     * Create a MediaItem and dispatch the categorization job.
     */
    private function createMediaItemAndDispatchJob(Batch $batch, string $rawData): void
    {
        $mediaItem = MediaItem::create([
            'batch_id' => $batch->id,
            'raw_data' => $rawData,
        ]);

        CategorizeMediaJob::dispatch($mediaItem);
    }
}
