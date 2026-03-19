<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
}
