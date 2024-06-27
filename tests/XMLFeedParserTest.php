<?php

use Mgrall\FeedToDb\Parser\XMLFeedParser;
use PHPUnit\Framework\TestCase;

class XMLFeedParserTest extends TestCase
{
    /**
     * Test that a valid XML string is successfully parsed.
     * @throws Exception
     */
    public function testParsesValidXmlString()
    {
        $xmlString = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <child>Example</child>
</root>
XML;

        $parser = new XMLFeedParser();
        $result = $parser->parse($xmlString);

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertEquals('Example', (string)$result->child);
    }

    /**
     * Test that an invalid XML string throws an exception.
     */
    public function testThrowsExceptionOnInvalidXml()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not read Data from XML');

        $invalidXmlString = '<root><child></child>';

        $parser = new XMLFeedParser();
        $parser->parse($invalidXmlString);
    }
}