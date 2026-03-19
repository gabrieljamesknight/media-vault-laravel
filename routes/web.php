<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaUploadController;
use App\Http\Controllers\DashboardController;

Route::get('/', [MediaUploadController::class, 'show'])->name('upload.show');
Route::post('/', [MediaUploadController::class, 'store'])->name('upload.store');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
