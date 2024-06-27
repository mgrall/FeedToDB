<?php

require 'vendor/autoload.php';

use Mgrall\FeedToDb\Factory;
use Mgrall\FeedToDb\config\Config;

$conf = new Config();

$dbDir = __DIR__ . '/storage/database';

if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true); // true for recursive creation
}


if (!file_exists(__DIR__ . '/storage/logs')) {
    mkdir(__DIR__ . '/storage/logs', 0777, true); // true for recursive creation
}

$logFile = __DIR__ . '/storage/logs/app.log';

if (!file_exists($logFile)) {
    $handle = fopen($logFile, 'a'); // 'a' mode will create the file if it doesn't exist
    if (!$handle) {
        // Handle error when file cannot be opened or created
        throw new Exception("Cannot open or create log file: " . $logFile);
    }
    fclose($handle); // Close the handle since it's just to ensure file creation
}




try {
    // Factory will choose the correct DatabaseInterface, ParserInterface and LoggerInterface implementation based on the provided config.
    $factory = new Factory(
        Config::get('database', 'sqlite01'),
        Config::get('data_source', 'feed.xml'),
        Config::get('logger', 'logger01')
    );

    // Start the import process. The DB will not clear when the program terminates,
    // also there is no PRIMARY KEY, so it will continue adding items to the catalog if it is run again.
    $factory->importFeed();
    echo "Success. Please note that I did NOT make entity_id the PRIMARY KEY so the program can be run multiple times for testing.\n";
} catch (Exception $e) {
    echo "There has been an error importing your feed: " . $e->getMessage();
}