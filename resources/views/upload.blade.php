@extends('layouts.app')

@section('title', 'Data Ingestion')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">MediaVault Data Ingestion</h1>
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Upload CSV File</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" 
                       class="block w-full text-sm text-gray-500 
                              file:mr-4 file:py-2 file:px-4 
                              file:rounded-md file:border-0 
                              file:text-sm file:font-semibold 
                              file:bg-blue-50 file:text-blue-700 
                              hover:file:bg-blue-100">
            </div>

            <div class="flex items-center">
                <div class="h-px bg-gray-300 flex-1"></div>
                <span class="px-4 text-gray-500 text-sm font-medium">OR</span>
                <div class="h-px bg-gray-300 flex-1"></div>
            </div>

            <div>
                <label for="raw_text" class="block text-sm font-medium text-gray-700 mb-2">Paste Raw Text</label>
                <textarea name="raw_text" id="raw_text" rows="6" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-3" 
                          placeholder="Paste unstructured media items here (e.g. Matrix DVD Good Condition)..."></textarea>
            </div>

            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Submit Data
                </button>
            </div>
        </form>
    </div>
@endsection
