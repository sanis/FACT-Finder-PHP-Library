<?php

namespace FACTFinder\Test\Adapter;

use FACTFinder\Adapter\Compare;

class CompareTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Adapter\Compare
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

        $this->adapter = new Compare($configuration, $request, $clientUrlBuilder);
    }

    public function testComparisonLoading()
    {
        $productIds = [];
        $productIds[] = 123;
        $productIds[] = 456;
        $productIds[] = 789;
        $this->adapter->setProductIds($productIds);
        $comparedRecords = $this->adapter->getComparedRecords();

        $this->assertEquals(3, count($comparedRecords), 'wrong number of records delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $comparedRecords[0], 'similar product is no record');
        $this->assertNotEmpty($comparedRecords[0], 'first similar record is empty');
        $this->assertEquals('123', $comparedRecords[0]->getId());
        $this->assertEquals('..schwarz..', $comparedRecords[0]->getField('Farbe'));
        $this->assertEquals('KHE Root 540 schwarz', $comparedRecords[0]->getField('Name'));
    }

    public function tesComparableAttributesOnly()
    {
        $productIds = [];
        $productIds[] = 123;
        $productIds[] = 456;
        $productIds[] = 789;
        $this->adapter->setProductIds($productIds);
        $this->adapter->setComparableAttributesOnly(true);
        $comparedRecords = $this->adapter->getComparedRecords();

        $this->assertEquals(3, count($comparedRecords), 'wrong number of records delivered');
        $this->assertInstanceOf('FACTFinder\Data\Record', $comparedRecords[0], 'similar product is no record');
        $this->assertNotEmpty($comparedRecords[0], 'first similar record is empty');
        $this->assertEquals('123', $comparedRecords[0]->getId());
        $this->assertEquals('..schwarz..', $comparedRecords[0]->getField('Farbe'));
    }

    public function testAttributesLoading()
    {
        $productIds = [];
        $productIds[] = 123;
        $productIds[] = 456;
        $productIds[] = 789;
        $this->adapter->setProductIds($productIds);
        $comparableAttributes = $this->adapter->getComparableAttributes();

        $this->assertEquals(7, count($comparableAttributes));
        $this->assertFalse($comparableAttributes['Hersteller']);
        $this->assertTrue($comparableAttributes['Farbe']);
        $this->assertTrue($comparableAttributes['Material']);
        $this->assertFalse($comparableAttributes['Modelljahr']);
    }
}
