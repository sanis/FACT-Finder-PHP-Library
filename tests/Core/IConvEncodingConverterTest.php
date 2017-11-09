<?php

namespace FACTFinder\Test\Core;

use FACTFinder\Core\IConvEncodingConverter;
use FACTFinder\Core\ManualConfiguration;

class IConvEncodingConverterTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\IConvEncodingConverter the converter under test
     */
    private $encodingConverter;

    public function setUp()
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped(
                'The iconv module has not been loaded.'
            );
        }

        parent::setUp();

        $configuration = new ManualConfiguration(
            [
                'pageContentEncoding' => 'ISO-8859-1',
                'clientUrlEncoding'   => 'ISO-8859-1',
            ]
        );

        $this->encodingConverter = new IConvEncodingConverter($configuration);
    }

    public function testEncodeContentForPage()
    {
        // Input is "ä" in UTF-8
        $utf8Content = "\xC3\xA4";

        // Page content is configured to be ISO-8859-1 encoded.
        $expectedPageContent = "\xE4";

        $this->assertEquals(
            $expectedPageContent,
            $this->encodingConverter->encodeContentForPage($utf8Content)
        );
    }

    public function testDecodeClientUrlData()
    {
        // Client "URL" is "ä" in ISO-8859-1
        $isoSring = "\xE4";

        // Output is expected to be UTF-8
        $expectedUtf8string = "\xC3\xA4";

        $this->assertEquals(
            $expectedUtf8string,
            $this->encodingConverter->decodeClientUrlData($isoSring)
        );
    }

    public function testEncodeClientUrlData()
    {
        // Input is "ä" in UTF-8
        $utf8string = "\xC3\xA4";

        // Client URL is configured to be ISO-8859-1 encoded.
        $expectedIsoString = "\xE4";

        $this->assertEquals(
            $expectedIsoString,
            $this->encodingConverter->encodeClientUrlData($utf8string)
        );
    }

    public function testDecodeClientUrlDataArray()
    {
        // Client "URL" contains umlauts in ISO-8859-1
        $isoArray = [
            0      => "\xE4",     // "ä"
            "\xDF" => [ // "ß" as key
                0 => "\xF6", // "ö"
                1 => "\xFC"  // "ü"
            ],
        ];

        // Output is expected to be UTF-8
        $expectedUtf8array = [
            0          => "\xC3\xA4",     // "ä"
            "\xC3\x9F" => [ // "ß" as key
                0 => "\xC3\xB6", // "ö"
                1 => "\xC3\xBC"  // "ü"
            ],
        ];;

        $this->assertEquals(
            $expectedUtf8array,
            $this->encodingConverter->decodeClientUrlData($isoArray)
        );
    }
}
