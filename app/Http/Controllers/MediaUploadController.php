<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;

class MediaUploadController extends Controller
{
    /**
     * Show the upload form.
     */
    public function show()
    {
        return view('upload');
    }

    /**
     * Process the uploaded data and create a pending batch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => 'nullable|file|mimes:csv,txt|max:10240',
            'raw_text' => 'nullable|string',
        ]);

        if (empty($validated['csv_file']) && empty($validated['raw_text'])) {
            return back()->withErrors(['error' => 'Please either upload a CSV file or paste raw text.']);
        }

        $filename = 'Raw Text Input';

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $filename = $file->getClientOriginalName();
            
            // Note: Saving the file to storage would happen here,
            // but for this step we are just tracking the Batch.
        }

        // Create a new Batch database record marking its status as 'pending'
        Batch::create([
            'original_filename' => $filename,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Data submitted successfully. A new batch has been created and is pending processing.');
    }
}
