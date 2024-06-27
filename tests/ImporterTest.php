<?php

use Mgrall\FeedToDb\Database\DatabaseInterface;
use Mgrall\FeedToDb\Importer;
use Mgrall\FeedToDb\Parser\ParserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Focus on verifying that the Importer class interacts with these dependencies correctly.
 * Ensure that methods are called ith the correct parameters.
 */
class ImporterTest extends TestCase
{
    private $parserMock;
    private $dbMock;
    private $loggerMock;
    private Importer $importer;

    /**
     * The internal logic for the implementation of ParserInterface, DatabaseInterface and LoggerInterface is tested in their own test files.
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->parserMock = $this->createMock(ParserInterface::class);
        $this->dbMock = $this->createMock(DatabaseInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->importer = new Importer($this->parserMock, $this->dbMock, $this->loggerMock);
    }

    public function testImportSuccess(): void
    {
        $schema = ['table_name' => 'test_table', 'columns' => ['col1', 'col2']];
        $tableName = 'test_table';
        $sourceFile = 'source_file.xml';
        $parsedData = [['col1' => 'val1', 'col2' => 'val2'], ['col1' => 'val3', 'col2' => 'val4']];

        $this->dbMock->expects($this->once())
            ->method('connect');

        $this->dbMock->expects($this->once())
            ->method('createStorage')
            ->with($schema);

        $this->parserMock->expects($this->once())
            ->method('parse')
            ->with($sourceFile)
            ->willReturn($parsedData);

        $this->dbMock->expects($this->exactly(2))
            ->method('insert')
            ->with(
                $this->equalTo($tableName),
                $this->logicalOr(
                    $this->equalTo($parsedData[0]),
                    $this->equalTo($parsedData[1])
                )
            );

        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->importer->importFeed($schema, $tableName, $sourceFile);
    }

    public function testImportThrowsException(): void
    {
        $schema = ['table_name' => 'test_table', 'columns' => ['col1', 'col2']];
        $tableName = 'test_table';
        $sourceFile = 'source_file.csv';

        $this->dbMock->expects($this->once())
            ->method('connect')
            ->will($this->throwException(new Exception('Connection error')));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('An error occurred', ['exception' => 'Connection error']);

        $this->importer->importFeed($schema, $tableName, $sourceFile);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testSetLogger(): void
    {
        $newLoggerMock = $this->createMock(LoggerInterface::class);

        $this->importer->setLogger($newLoggerMock);

        $reflection = new ReflectionClass($this->importer);
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);

        $this->assertSame($newLoggerMock, $property->getValue($this->importer));
    }
}