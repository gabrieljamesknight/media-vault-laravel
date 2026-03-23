<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaUploadController;
use App\Http\Controllers\DashboardController;

Route::get('/', [MediaUploadController::class, 'show'])->name('upload.show');
Route::post('/', [MediaUploadController::class, 'store'])->name('upload.store');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
Route::get('/dashboard/export', [DashboardController::class, 'exportCsv'])->name('dashboard.export');
Route::get('/dashboard/export/batch/{batch}', [DashboardController::class, 'exportBatchCsv'])->name('dashboard.export.batch');
