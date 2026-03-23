<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\MediaItem;
use App\Services\CatalogExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    /**
     * The CatalogExportService instance.
     */
    protected CatalogExportService $exportService;

    /**
     * Create a new controller instance.
     */
    public function __construct(CatalogExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Display the media items results dashboard.
     */
    public function index(): View
    {
        $batches = Batch::with('mediaItems')
            ->latest()
            ->get();

        $genres = MediaItem::distinct()
            ->pluck('genre')
            ->sort()
            ->values();

        $mediaFormats = MediaItem::distinct()
            ->pluck('media_format')
            ->sort()
            ->values();

        return view('dashboard', compact('batches', 'genres', 'mediaFormats'));
    }

    /**
     * Get the dashboard data as JSON.
     */
    public function data(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $genre = $request->query('genre');
        $mediaFormat = $request->query('media_format');

        $filterClosure = function ($q) use ($search, $genre, $mediaFormat) {
            if ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('product_name', 'like', '%' . $search . '%')
                         ->orWhere('artist_or_director', 'like', '%' . $search . '%')
                         ->orWhere('raw_data', 'like', '%' . $search . '%');
                });
            }
            if ($genre) {
                $q->where('genre', $genre);
            }
            if ($mediaFormat) {
                $q->where('media_format', $mediaFormat);
            }
        };

        $query = Batch::with(['mediaItems' => $filterClosure]);

        if ($search || $genre || $mediaFormat) {
            $query->whereHas('mediaItems', $filterClosure);
        }

        $batches = $query->latest()->get();

        return response()->json([
            'batches' => $batches
        ]);
    }

    /**
     * Export all processed media items to a CSV file.
     */
    public function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo $this->exportService->generateCsv();
        }, 'structured_catalog.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /**
     * Export processed media items for a specific batch to a CSV file.
     */
    public function exportBatchCsv(Batch $batch): StreamedResponse
    {
        return response()->streamDownload(function () use ($batch) {
            echo $this->exportService->generateCsv($batch->id);
        }, "batch_{$batch->id}_export.csv", [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }
}
