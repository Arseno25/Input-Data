<?php

$artisanPath = __DIR__ . '/artisan';

if (!file_exists($artisanPath)) {
    die("File 'artisan' tidak ditemukan di path: $artisanPath\n");
}

exec("php $artisanPath queue:work --sleep=3 --tries=1 --timeout=60 --backoff=0", $queueOutput, $queueStatus);

if ($queueStatus === 0) {
    echo "Command 'php artisan queue:work --sleep=3 --tries=1 --timeout=60 --backoff=0' berhasil dijalankan di background.\n";
} else {
    echo "Command gagal dijalankan dengan status: $queueStatus\n";
    print_r($queueOutput);
}
