<?php

namespace Mgrall\FeedToDb\Database;

use Exception;

/**
 * An interface that facilitates inserting data into databases.
 */
interface DatabaseInterface
{
    /**
     * Constructor that establishes a database connection.
     *
     * @param string $dsn The Data Source Name for the PDO connection.
     * @param string $username Optional. The username for the database connection.
     * @param string $password Optional. The password for the database connection.
     * @param array $options Optional. An array of options for the PDO connection.
     */
    public function __construct(string $dsn, string $username = '', string $password = '', array $options = []);

    /**
     * Sets up the database connection.
     *
     * @return void
     * @throws Exception If the connection cannot be established.
     */
    public function connect(): void;

    /**
     * Creates a table / collection to store data.
     *
     * @param $schema
     * @return void
     * @throws Exception If a connection wasn't established first.
     */
    public function createStorage($schema): void;

    /**
     * Inserts data into the specified table / collection.
     *
     * @param string $table The name of the table / collection.
     * @param mixed $data Associative array of the data to be inserted.
     * @return void
     * @throws Exception If a connection wasn't established first.
     */
    public function insert(string $table, mixed $data): void;
}