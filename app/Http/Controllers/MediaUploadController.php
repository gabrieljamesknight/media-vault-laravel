<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaUploadController extends Controller
{
    /**
     * The MediaUploadService instance.
     */
    private MediaUploadService $mediaUploadService;

    /**
     * Create a new controller instance.
     */
    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }

    /**
     * Show the upload form.
     */
    public function show(): View
    {
        return view('upload');
    }

    /**
     * Process the uploaded data and create a pending batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv_file' => 'nullable|file|mimes:csv,txt|max:10240',
            'raw_text' => 'nullable|string',
        ]);

        if (empty($validated['csv_file']) && empty($validated['raw_text'])) {
            return back()->withErrors(['error' => 'Please either upload a CSV file or paste raw text.']);
        }

        $this->mediaUploadService->processUpload(
            $validated['raw_text'] ?? null,
            $request->file('csv_file')
        );

        return back()->with('success', 'Data submitted successfully. A new batch has been created and is pending processing.');
    }
}
