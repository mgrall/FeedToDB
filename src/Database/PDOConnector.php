<?php

namespace Mgrall\FeedToDb\Database;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

/**
 * Connects with relational Databases whose drivers implement the PDO interface (SQLITE, MySQL, IBM...)
 */
class PDOConnector implements DatabaseInterface
{
    private $connection;
    private string $dsn;
    private string $username;
    private string $password;
    private array $options;

    public function __construct(string $dsn, string $username = '', string $password = '', array $options = [])
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;
    }

    /**
     * Establishes a connection to the database using PDO.
     *
     * @return void
     * @throws Exception if the attempt to connect to the requested database fails.
     */
    public function connect(): void
    {
        try {
            $this->connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
            // Default as of PHP 8.0.0
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Connection error: ' . $e->getMessage());
        }
    }

    /**
     * Method to set the PDO object from outside for testing.
     *
     * @param Class::PDO $pdo A PDO-Mock.
     * @return void
     */
    public function setPDO($pdo): void
    {
        $this->connection = $pdo;
    }

    /**
     * Creates a table inside the relational database.
     *
     * @param array $schema An associative array containing the table_name and all column names seperated by commas
     * @return void
     * @throws Exception If a connection wasn't established first
     */
    public function createStorage($schema): void
    {
        if ($this->connection === null) {
            throw new Exception("No database connection. Please connect first.");
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $schema['table_name'] . " (" . $schema['columns'] . ")";

        $this->connection->exec($sql);
    }


    /**
     * Inserts the data into the specified database table.
     *
     * @param String $table The name of the table the data is to be inserted in.
     * @param mixed $data A one dimensional associative array of key-value pairs (column-value)
     * @return void
     * @throws InvalidArgumentException If the data array is multidimensional.
     * @throws Exception If a connection wasn't established first
     */
    public function insert(string $table, mixed $data): void
    {
        if ($this->connection === null) {
            throw new Exception("No database connection. Please connect first.");
        }

        // Check for multidimensional arrays
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Throw an exception or handle this error as needed
                throw new InvalidArgumentException(
                    "Multidimensional array detected at key '$key'. Expected a single-level associative array."
                );
            }
        }

        $columns = implode(',', array_keys($data));
        $values = implode(',', array_map(function ($val) {
            return ":$val";
        }, array_keys($data)));
        $stmt = $this->connection->prepare("INSERT INTO $table ($columns) VALUES ($values)");
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
    }
}