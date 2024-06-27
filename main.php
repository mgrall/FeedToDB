<?php

// Set up the application environment.
require_once 'setup.php';
// Load Composer dependencies.
require 'vendor/autoload.php';

use Mgrall\FeedToDb\Factory;
use Mgrall\FeedToDb\config\Config;

$conf = new Config();

try {
    // Factory will choose the correct DatabaseInterface, ParserInterface and LoggerInterface implementation based on the provided config.
    $factory = new Factory(
        Config::get('database', 'sqlite01'),
        Config::get('data_source', 'feed.xml'),
        Config::get('logger', 'logger01')
    );

    // Start the import process. The DB will not clear when the program terminates,
    // also I removed the PRIMARY KEY, so it will continue adding items to the catalog if it is run again.
    echo "Attempting to import data..." . PHP_EOL;
    $factory->importFeed();
    echo "Success. Please note that I did not make entity_id the PRIMARY KEY, so the program can be run multiple times for testing." . PHP_EOL;
} catch (Exception $e) {
    echo "There has been an error importing your feed: " . $e->getMessage();
}
