<?php

namespace Mgrall\FeedToDb\Parser;

use Exception;

interface ParserInterface
{
    /**
     * @param string $source The file path.
     * @return mixed A readable object representing the file.
     * @throws Exception If file could not be read.
     */
    public function parse(string $source): mixed;
}