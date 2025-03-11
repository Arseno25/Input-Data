<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/download-pdf/{filename}', function ($filename) {
    $filename = urldecode(basename($filename));

    // Check if file exists in public disk
    if (!Storage::disk('public')->exists("exports/{$filename}")) {
        abort(404, 'File not found');
    }

    // Download using Storage facade
    return Storage::disk('public')->download("exports/{$filename}");
})->name('download.pdf');
