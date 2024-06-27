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
class Factory implements FactoryInterface
{
    private ParserInterface $parser;
    private DatabaseInterface $db;
    private LoggerInterface $logger;
    private array $dbConfig;
    private array $dataSource;

    /**
     * Sets up DatabaseInterface, ParserInterface and LoggerInterface based on config.
     * @throws Exception
     */
    public function __construct($dbConfig, $dataSource, $loggerConfig)
    {
        $this->createLogger($loggerConfig['type']);
        $this->dbConfig = $dbConfig;
        $this->dataSource = $dataSource;
        $this->createDatabase(
            $dbConfig['type'],
            $dbConfig['dsn'],
            $dbConfig['username'] ?? '',
            $dbConfig['password'] ?? '',
            $dbConfig['options'] ?? []
        );
        $this->createParser($dataSource['type']);
    }

    /**
     * Inserts the sourceFile contents into the specified database table or collection with the specified schema.
     * @return void
     * @throws Exception
     */
    public function importFeed(): void
    {
        try {
            // Setup
            $this->db->connect();
            $this->db->createStorage($this->dbConfig['schema']);
            $data = $this->parser->parse($this->dataSource['path']);

            // Insert data
            foreach ($data as $entry) {
                $this->db->insert($this->dbConfig['schema']['table_name'], (array)$entry);
                //echo "inserted: " . implode(",", (array)$entry) . "\n";
                // $this->logger->info('Inserted ', [implode(",", (array)$entry) . PHP_EOL]);
            }
        } catch (Exception $e) {
            $this->logger->error('An error occurred', ['exception' => $e->getMessage()]);
            throw $e;
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

    /**
     * @throws Exception
     */
    private function createDatabase(
        $type,
        $dsn,
        string $username,
        string $password,
        array $options
    ): void {
        if ($type == 'sqlite') {
            $this->db = new PDOConnector($dsn);
        }
        else throw new Exception('\'' . $type . '\' not found in config.');
    }

    /**
     * @throws Exception
     */
    private function createParser($type): void
    {
        if ($type == 'xml_feed') {
            $this->parser = new XMLFeedParser();
        }
        else throw new Exception('\'' . $type . '\' not found in config.');
    }

    /**
     * @throws Exception
     */
    private function createLogger($type): void
    {
        if ($type == 'FileFeedLogger') {
            $this->logger = new FileFeedLogger();
        }
        else throw new Exception('\'' . $type . '\' not found in config.');
    }
}