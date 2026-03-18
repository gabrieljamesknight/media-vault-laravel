<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaUploadController;

Route::get('/', [MediaUploadController::class, 'show'])->name('upload.show');
Route::post('/', [MediaUploadController::class, 'store'])->name('upload.store');
