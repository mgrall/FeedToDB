<?php

namespace Mgrall\FeedToDb;

use Mgrall\FeedToDb\Database\DatabaseInterface;
use Mgrall\FeedToDb\Parser\ParserInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * An interface that connects Parser, Database and Logger.
 */
interface ImporterInterface extends LoggerAwareInterface
{
    public function __construct(ParserInterface $parser, DatabaseInterface $db, LoggerInterface $logger);

    /**
     * Inserts data from feed into the database
     * @return void
     */
    public function importFeed(): void;
}