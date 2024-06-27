<?php

namespace Mgrall\FeedToDb;

use Exception;
use Mgrall\FeedToDb\Database\DatabaseInterface;
use Mgrall\FeedToDb\Database\PDOConnector;
use Mgrall\FeedToDb\Logger\FileFeedLogger;
use Mgrall\FeedToDb\Parser\ParserInterface;
use Mgrall\FeedToDb\Parser\XMLFeedParser;
use Psr\Log\LoggerInterface;

/**
 * A class that parses data with a ParserInterface and inserts it into a Database connected via DatabaseInterface.
 */
class Importer implements ImporterInterface
{
    private ParserInterface $parser;
    private DatabaseInterface $db;
    private LoggerInterface $logger;
    private array $dbConfig;
    private array $dataSource;

    public function __construct($dbConfig, $dataSource, $loggerConfig)
    {
        $this->dbConfig = $dbConfig;
        $this->dataSource = $dataSource;
        $this->createDatabase($dbConfig['type'], $dbConfig['dsn'], $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
        $this-> createParser($dataSource['type']);
        $this->createLogger($loggerConfig['type']);

    }

    private function createDatabase($type, $dsn, string $username = '', string $password = '', array $options = []): void
    {
        if ($type == 'sqlite') $this->db = new PDOConnector($dsn);
    }

    private function createParser($type): void
    {
        if ($type == 'xml_feed') $this->parser = new XMLFeedParser();
    }

    private function createLogger($type): void
    {
        if ($type == 'FileFeedLogger') $this->logger = new FileFeedLogger();
    }

    /**
     * Inserts the sourceFile contents into the specified database table or collection with the specified schema.
     * @return void
     */
    public function importFeed(): void
    {
        try {
            $this->db->connect();

            $this->db->createStorage($this->dbConfig['schema']);

            $data = $this->parser->parse($this->dataSource['path']);
            foreach ($data as $entry) {
                $this->db->insert($this->dbConfig['table_name'], (array)$entry);
                // echo "inserted: " . implode(",", (array)$entry) . "\n";
                // $this->logger->info('Inserted ', [implode(",", (array)$entry) . PHP_EOL]);
            }
        } catch (Exception $e) {
            $this->logger->error('An error occurred', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Set the logger.
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}