<?php

namespace FACTFinder\Test\Adapter;

use FACTFinder\Loader as FF;

class ImportTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Adapter\Import
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $this->adapter = FF::getInstance(
            'Adapter\Import',
            self::$dic['configuration'],
            self::$dic['request'],
            self::$dic['clientUrlBuilder']
        );
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
