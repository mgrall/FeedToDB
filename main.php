<?php

require 'vendor/autoload.php';

use Mgrall\FeedToDb\Database\PDOConnector;
use Mgrall\FeedToDb\Logger\FileFeedLogger;
use Mgrall\FeedToDb\Importer;
use Mgrall\FeedToDb\Parser\XMLFeedParser;
use Mgrall\FeedToDb\config\Config;


$logger = new FileFeedLogger();
$conf = new Config();

$factory = new Importer(
    Config::get('database', 'sqlite01'),
    Config::get('data_source', 'feed.xml'),
    Config::get('logger', 'logger01')
);

$factory->importFeed();

try {
    // Construct our handler with specific interface implementations for Parser, DB and Logger, depending on use case.
    // In this case we want import the contents of feed.xml into a sqlite database while utilizing a custom logger.
    $importer = new Importer(
        new XMLFeedParser(),
        new PDOConnector(Config::get('database', 'sqlite')['dsn']),
        new FileFeedLogger()
    );
    // Start the import process with the specified configurations.
    $importer->importFeed(
        Config::get('database', 'sqlite')['schema'],
        Config::get('database', 'sqlite')['schema']['table_name'],
        Config::get('data_source', 'xml_path')
    );
} catch (Exception $e) {
    $this->logger->error('An error occurred', ['exception' => $e->getMessage()]);
    throw new Exception($e->getMessage());
}

