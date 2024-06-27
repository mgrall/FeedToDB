<?php

namespace Mgrall\FeedToDb;

use Mgrall\FeedToDb\Database\DatabaseInterface;
use Mgrall\FeedToDb\Database\PDOConnector;
use Mgrall\FeedToDb\Logger\FileFeedLogger;
use Mgrall\FeedToDb\Parser\ParserInterface;
use Mgrall\FeedToDb\Parser\XMLFeedParser;
use Psr\Log\LoggerInterface;

class Factory
{
    private ParserInterface $parser;
    private DatabaseInterface $db;
    private LoggerInterface $logger;

    public function __construct($dbConfig, $dataSource, $loggerConfig)
    {
        $this->createDatabase($dbConfig['type'], $dbConfig['dsn'], $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        $this-> createParser($dataSource['type']);
        $this->createLogger($loggerConfig['type']);

    }

    private function createDatabase($type, $dsn, string $username = '', string $password = '', array $options = []): void
    {
        if ($type == 'sqlite') $this->db = new PDOConnector($dsn);
    }

    public function createParser($type)
    {
        if ($type == 'xml_feed') return new XMLFeedParser();
    }

    public function createLogger($type)
    {
        if ($type == 'FileFeedLogger') return new FileFeedLogger();
    }

    public function startImport($dbConfig)
    {
        $importer = new Importer($this->parser, $this->db, $this->logger);
        $importer->importFeed();
    }
}