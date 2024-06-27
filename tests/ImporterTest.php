<?php

use Mgrall\FeedToDb\Factory;
use Mgrall\FeedToDb\Database\DatabaseInterface;
use Mgrall\FeedToDb\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ImporterTest extends TestCase
{
    private $dbConfig;
    private $dataSource;
    private $loggerConfig;
    private $mockParser;
    private $mockDatabase;
    private $mockLogger;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->dbConfig = [
            'type' => 'sqlite',
            'dsn' => 'sqlite::memory:',
            'schema' => [
                'table_name' => 'test_table',
                'fields' => ['id', 'name', 'value']
            ]
        ];

        $this->dataSource = [
            'type' => 'xml_feed',
            'path' => 'path/to/feed.xml'
        ];

        $this->loggerConfig = [
            'type' => 'FileFeedLogger'
        ];

        $this->mockParser = $this->createMock(ParserInterface::class);
        $this->mockDatabase = $this->createMock(DatabaseInterface::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testConstructor()
    {
        $importer = new Factory($this->dbConfig, $this->dataSource, $this->loggerConfig);

        $this->assertInstanceOf(Factory::class, $importer);
    }

    /**
     * @throws Exception
     */
    public function testImportFeed()
    {
        $data = [
            (object)['id' => 1, 'name' => 'item1', 'value' => 'value1'],
            (object)['id' => 2, 'name' => 'item2', 'value' => 'value2']
        ];

        $this->mockDatabase->expects($this->once())
            ->method('connect');

        $this->mockDatabase->expects($this->once())
            ->method('createStorage')
            ->with($this->dbConfig['schema']);

        // Parser normally only works with a real file, so we have to make it return data manually.
        $this->mockParser->expects($this->once())
            ->method('parse')
            ->with($this->dataSource['path'])
            ->willReturn($data);

        $this->mockDatabase->expects($this->exactly(2))
            ->method('insert')
            ->with(
                $this->callback(function($table) {
                    return $table === $this->dbConfig['schema']['table_name'];
                }),
                $this->callback(function($entry) use ($data) {
                    return in_array($entry, [(array)$data[0], (array)$data[1]]);
                })
            );

        $importer = new Factory($this->dbConfig, $this->dataSource, $this->loggerConfig);
        $importer->setLogger($this->mockLogger);

        // Use reflection to set the private properties.
        $reflection = new ReflectionClass($importer);
        $parserProperty = $reflection->getProperty('parser');
        $parserProperty->setAccessible(true);
        $parserProperty->setValue($importer, $this->mockParser);

        $databaseProperty = $reflection->getProperty('db');
        $databaseProperty->setAccessible(true);
        $databaseProperty->setValue($importer, $this->mockDatabase);

        $importer->importFeed();
    }

    public function testImportFeedThrowsExceptionOnError()
    {
        $this->mockDatabase->expects($this->once())
            ->method('connect')
            ->will($this->throwException(new Exception('Connection error')));

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with('An error occurred', ['exception' => 'Connection error']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Connection error');

        $importer = new Factory($this->dbConfig, $this->dataSource, $this->loggerConfig);
        $importer->setLogger($this->mockLogger);

        // Use reflection to set the private properties.
        $reflection = new ReflectionClass($importer);
        $databaseProperty = $reflection->getProperty('db');
        $databaseProperty->setAccessible(true);
        $databaseProperty->setValue($importer, $this->mockDatabase);

        $importer->importFeed();
    }
}
