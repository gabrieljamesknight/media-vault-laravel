<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Services\CatalogExportService;
use Illuminate\Http\JsonResponse;
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

        return view('dashboard', compact('batches'));
    }

    /**
     * Get the dashboard data as JSON.
     */
    public function data(): JsonResponse
    {
        $batches = Batch::with('mediaItems')
            ->latest()
            ->get();

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
}
