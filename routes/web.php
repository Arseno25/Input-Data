<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/download-pdf/{filename}', function ($filename) {
    $filename = urldecode(basename($filename));
    $path = "exports/{$filename}";

    if (!Storage::disk('public')->exists($path)) {
        Log::error('PDF file not found', ['path' => $path]);
        abort(404, 'File not found');
    }

    Log::info('Downloading PDF', ['path' => $path]);
    return Storage::disk('public')->download($path);
})->name('download.pdf');
