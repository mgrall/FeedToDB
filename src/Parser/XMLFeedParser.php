<?php

namespace Mgrall\FeedToDb\Parser;


use Exception;

/**
 * Parses XML files, utilizing SimpleXML.
 */
class XMLFeedParser implements ParserInterface
{

    /**
     * Parses a local XML file utilizing SimpleXML.
     *
     * @param String $source The XML file path.
     * @return object|bool SimpleXMLElement, essentially a fancy associative array.
     * @throws Exception
     */
    public function parse(string $source): object|bool
    {
        $xml = simplexml_load_file($source);


        if ($xml === false) {
            throw new Exception('Could not read Data from XML');
        }
        return $xml;
    }
}