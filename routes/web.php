<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;



Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/download-pdf/{filename}', function ($filename) {
    $filename = urldecode(basename($filename));
    $filePath = storage_path("app/public/exports/{$filename}");

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return response()->download($filePath);
})->name('download.pdf');
