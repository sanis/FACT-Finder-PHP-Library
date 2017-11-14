<?php

namespace FACTFinder\Test\Core\Client;

class RequestParserTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\RequestParser the parser under test
     */
    private $requestParser;

    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration(static::class);
        $encodingConverter = $this->getConverter($configuration);
        $requestParser = $this->getRequestParser($configuration, $encodingConverter);

        $this->requestParser = $requestParser;

    }

    public function testParametersFromSuperglobal()
    {
        $_SERVER['QUERY_STRING'] = 'a=b&c=d';

        // We expect to get the result UTF-8 encoded
        $this->assertParameters(
            [
                'a'       => 'b',
                'c'       => 'd',
                'channel' => 'de', // is always added
            ]
        );
    }

    public function testClientUrlEncoding()
    {
        // 'ä=ö&ü=ß' in ISO-8859-1
        $_SERVER['QUERY_STRING'] = '%E4=%F6&%FC=%DF&%2B+%7E=%7E+%2B';

        // We expect to get the result UTF-8 encoded
        $this->assertParameters(
            [
                "\xC3\xA4"  => "\xC3\xB6", // 'ä' => 'ö'
                "\xC3\xBC"  => "\xC3\x9F", // 'ü' => 'ß'
//                "\x2B \x7E" => "\x7E \x2B", // '+ ~' => '~ +'
                'channel'   => 'de', // is always added
            ]
        );
    }

    public function testParameterConversion()
    {
        $_SERVER['QUERY_STRING'] = 'keywords=test';

        $this->assertParameters(
            [
                'query'   => 'test',
                'channel' => 'de',
            ]
        );
    }

    public function testRequestTarget()
    {
        $_SERVER['REQUEST_URI'] = '/index.php?foo=bar';

        $this->assertEquals(
            '/index.php',
            $this->requestParser
                ->getRequestTarget()
        );
    }

    public function testRequestTargetEncoding()
    {
        // 'indäx.php' in ISO-8859-1
        $_SERVER['REQUEST_URI'] = '/ind%E4x.php?foo=bar';

        $this->assertEquals(
            "/ind\xC3\xA4x.php",
            $this->requestParser
                ->getRequestTarget()
        );
    }

    function testSeoParameterConversion()
    {
        $_SERVER['REQUEST_URI'] = '/s/a%20b';

        $expectedParameters = [
            'seoPath' => '/a b',
        ];
        $actualParameters = $this->requestParser
            ->getClientRequestParameters()
            ->getArray();
        $this->assertEquals($expectedParameters, $actualParameters);
    }

    private function assertParameters($expectedParameters)
    {
        $actualParameters = $this->requestParser
            ->getRequestParameters()
            ->getArray();

        $this->assertEquals($expectedParameters, $actualParameters);
    }
}
