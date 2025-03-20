<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return redirect('/lecturer');
});

Route::get('/download-excel/{filename}', function ($filename) {
    $filename = urldecode(basename($filename));
    $path = "exports/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        \Log::error('Excel file not found', ['path' => $path]);
        abort(404, 'File not found');
    }

    return Storage::disk('public')->download($path);
})->name('download.excel');

Route::get('/download-pdf/{filename}', function ($filename) {
    $filename = urldecode(basename($filename));
    $path = "exports/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        \Log::error('PDF file not found', ['path' => $path]);
        abort(404, 'File not found');
    }

    return Storage::disk('public')->download($path);
})->name('download.pdf');
