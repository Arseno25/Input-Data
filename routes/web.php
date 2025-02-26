<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/download-pdf/{filename}', function ($filename) {
    return response()->file(storage_path("app/public/exports/{$filename}"));
})->name('download.pdf');

