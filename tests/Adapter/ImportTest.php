<?php

namespace FACTFinder\Test\Adapter;

use FACTFinder\Adapter\Import;

class ImportTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Adapter\Import
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration(static::class);
        $encodingConverter = $this->getConverter($configuration);
        $requestParser = $this->getRequestParser($configuration, $encodingConverter);
        $clientUrlBuilder = $this->getClientUrlBuilder($configuration, $requestParser, $encodingConverter);
        $requestFactory = $this->getRequestFactory($configuration, $requestParser);
        $request = $this->getRequest($requestFactory);

        $this->adapter = new Import($configuration, $request, $clientUrlBuilder);
    }

    public function testDataImport()
    {
        $this->adapter->triggerDataImport();
    }

    public function testSuggestImport()
    {
        $this->adapter->triggerSuggestImport();
    }

    public function testRecommendationImport()
    {
        $this->adapter->triggerRecommendationImport();
    }

    public function testMultipleImports()
    {
        $oReport1 = $this->adapter->triggerDataImport();
        $oReport2 = $this->adapter->triggerRecommendationImport();
        $this->assertNotSame($oReport1, $oReport2);
    }
}
