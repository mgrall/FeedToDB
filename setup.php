<?php

function createDirectory($path, $permissions = 0777): void
{
    if (!file_exists($path)) {
        mkdir($path, $permissions, true);
        echo "Created directory: $path\n";
    }
}

// Create necessary directories.
createDirectory(__DIR__ . '/storage/logs');
createDirectory(__DIR__ . '/storage/database');

// Create log file.
$logFile = __DIR__ . '/storage/logs/app.log';
if (!file_exists($logFile)) {
    $handle = fopen($logFile, 'a');
    if (!$handle) {
        throw new Exception("Cannot open or create log file: " . $logFile);
    }
    fclose($handle);
}

echo "Setup completed.\n";