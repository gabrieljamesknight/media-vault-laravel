@extends('layouts.app')

@section('title', 'Dashboard')

@section('body-attributes')
    x-data="dashboard()" x-init="initPolling()"
@endsection

@section('content')
    <header>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center py-6">
            <h1 class="text-3xl font-bold leading-tight text-gray-900">Processing Results</h1>
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard.export') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download CSV
                </a>
                <div class="flex items-center space-x-2 text-sm text-gray-500 bg-gray-50 px-3 py-2 rounded-full border border-gray-100 shadow-sm">
                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="font-medium">Live Updates On</span>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 py-8 sm:px-0">
                <div class="mb-6 flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="flex-1">
                        <input type="text" x-model.debounce.300ms="search" placeholder="Search product, artist, or raw data..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md py-2.5 px-4">
                    </div>
                    <div class="w-full sm:w-48">
                        <select x-model="genre" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md py-2.5 px-4">
                            <option value="">All Genres</option>
                            @foreach($genres as $genreOption)
                                <option value="{{ $genreOption }}">{{ $genreOption === null || $genreOption === '' || strtolower((string)$genreOption) === 'null' ? 'Uncategorized' : $genreOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-48">
                        <select x-model="mediaFormat" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md py-2.5 px-4">
                            <option value="">All Formats</option>
                            @foreach($mediaFormats as $formatOption)
                                <option value="{{ $formatOption }}">{{ $formatOption === null || $formatOption === '' || strtolower((string)$formatOption) === 'null' ? 'Uncategorized' : $formatOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <template x-if="batches.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No batches processed</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by ingesting some media data.</p>
                        <div class="mt-6">
                            <a href="{{ route('upload.show') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Go to Ingest
                            </a>
                        </div>
                    </div>
                </template>

                <template x-for="batch in batches" :key="batch.id">
                    <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="md:flex md:items-center md:justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-4">
                                        <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-2xl sm:truncate">
                                            Batch #<span x-text="batch.id"></span> - <span x-text="batch.original_filename || 'Raw Text Input'"></span>
                                        </h2>
                                        <template x-if="batch.status === 'completed'">
                                            <a :href="`{{ route('dashboard.export.batch', ':id') }}`.replace(':id', batch.id)" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Download Batch
                                            </a>
                                        </template>
                                    </div>
                                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="formatDate(batch.created_at)"></span>
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                  :class="batch.status === 'completed' ? 'bg-green-100 text-green-800' : (batch.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')"
                                                  x-text="batch.status.charAt(0).toUpperCase() + batch.status.slice(1)">
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex-shrink-0 md:mt-0 md:ml-4 w-48">
                                    <div class="text-sm font-medium text-gray-700 mb-1 flex justify-between">
                                        <span>Progress</span>
                                        <span x-text="getBatchProgressText(batch)"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" :style="`width: ${getBatchProgressPercentage(batch)}%`"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col">
                                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raw Input</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artist/Director</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Genre</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <template x-if="batch.media_items.length === 0">
                                                        <tr>
                                                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                                No items found in this batch.
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <template x-for="item in batch.media_items" :key="item.id">
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" :title="item.raw_data" x-text="item.raw_data"></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="item.product_name || '---'"></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.artist_or_director || '---'"></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.media_format || '---'"></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.genre || '---'"></td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.condition || '---'"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        function dashboard() {
            return {
                search: '',
                genre: '',
                mediaFormat: '',
                batches: @json($batches),
                
                initPolling() {
                    this.$watch('search', () => this.fetchData());
                    this.$watch('genre', () => this.fetchData());
                    this.$watch('mediaFormat', () => this.fetchData());

                    setInterval(() => {
                        this.fetchData();
                    }, 3000);
                },

                async fetchData() {
                    try {
                        const params = new URLSearchParams({
                            search: this.search,
                            genre: this.genre,
                            media_format: this.mediaFormat
                        });
                        
                        const response = await fetch(`{{ route('dashboard.data') }}?${params.toString()}`);
                        const data = await response.json();
                        this.batches = data.batches;
                    } catch (error) {
                        console.error('Failed to fetch dashboard data:', error);
                    }
                },

                getBatchProgressText(batch) {
                    const total = batch.media_items.length;
                    const completed = batch.media_items.filter(item => item.product_name !== null).length;
                    return `${completed}/${total}`;
                },

                getBatchProgressPercentage(batch) {
                    const total = batch.media_items.length;
                    if (total === 0) return 0;
                    const completed = batch.media_items.filter(item => item.product_name !== null).length;
                    return Math.round((completed / total) * 100);
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric', 
                        year: 'numeric',
                        hour: 'numeric',
                        minute: 'numeric'
                    });
                }
            }
        }
    </script>
@endpush
