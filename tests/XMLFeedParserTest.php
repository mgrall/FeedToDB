<?php


use Mgrall\FeedToDb\Parser\XMLFeedParser;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class XMLFeedParserTest extends TestCase
{
    private XMLFeedParser $parser;
    protected function setUp(): void
    {
        $this->parser = new XMLFeedParser();
    }

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

        // Create a virtual file.
        $vfsRoot = vfsStream::setup('exampleDir');
        $file = vfsStream::newFile('example.xml')
            ->withContent($xmlString)
            ->at($vfsRoot);

        $result = $this->parser->parse($file->url());

        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertEquals('Example', (string)$result->child);
    }

    /**
     * Test that an invalid XML file throws an exception.
     */
    public function testThrowsExceptionOnInvalidXml()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not read Data from XML');

        $this->parser->parse('invalidFile.xml');
    }
}