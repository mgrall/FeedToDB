<?php

use PHPUnit\Framework\TestCase;
use Mgrall\FeedToDb\Database\PDOConnector;

class PDOConnectorTest extends TestCase
{
    private PDOConnector $connector;
    private string $dsn = 'sqlite::memory:';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        // Initialize the connector with SQLite.
        $this->connector = new PDOConnector($this->dsn);
    }

    public function testConstructor(): void
    {
        $this->assertNotNull($this->connector);
    }

    /**
     * @throws Exception
     */
    public function testConnect(): void
    {
        $this->connector->connect();
        $this->assertNotNull($this->connector);
    }

    /**
     * @throws Exception
     */
    public function testCreateStorage(): void
    {
        // Create mock schema.
        $this->connector->connect();
        $schema = [
            'table_name' => 'test',
            'columns' => 'id INTEGER PRIMARY KEY, name TEXT NOT NULL'
        ];

        $this->connector->createStorage($schema);

        // Retrieve private PDO connection.
        $reflection = new ReflectionClass($this->connector);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $pdo = $connectionProperty->getValue($this->connector);

        // Check if the table exists.
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='test'");
        $result = $stmt->fetchAll();
        $this->assertCount(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testInsertData(): void
    {
        // Create mock table and data.
        $this->connector->connect();
        $schema = [
            'table_name' => 'test',
            'columns' => 'id INTEGER PRIMARY KEY, name TEXT NOT NULL'
        ];
        $this->connector->createStorage($schema);

        $data = ['id' => 1, 'name' => 'John Doe'];
        $this->connector->insert('test', $data);

        // Retrieve private PDO connection.
        $reflection = new ReflectionClass($this->connector);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $pdo = $connectionProperty->getValue($this->connector);

        // Verify the data is inserted correctly.
        $stmt = $pdo->query("SELECT * FROM test WHERE id = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertSame($data, $result);
    }

    protected function tearDown(): void
    {
        unset($this->connector);
    }
}
