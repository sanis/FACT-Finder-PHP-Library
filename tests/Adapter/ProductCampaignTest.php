<?php

namespace FACTFinder\Test\Adapter;

use FACTFinder\Adapter\ProductCampaign;

class ProductCampaignTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Adapter\ProductCampaign
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

        $this->adapter = new ProductCampaign($configuration, $request, $clientUrlBuilder);
    }

    public function testProductCampaignLoading()
    {
        $productNumbers = [];
        $productNumbers[] = 123;
        $productNumbers[] = 456; // should be ignored
        $this->adapter->setProductNumbers($productNumbers);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertInstanceOf('FACTFinder\Data\Campaign', $campaigns[0]);

        $this->assertTrue($campaigns->hasRedirect());
        $this->assertEquals('http://www.fact-finder.de', $campaigns->getRedirectUrl());

        $this->assertTrue($campaigns->hasFeedback());
        $expectedFeedback = "test feedback";
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('html header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('9'));

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertEquals('KHE', $products[0]->getField('Brand'));

        $this->assertFalse($campaigns->hasActiveQuestions());
    }

    public function testIdsOnlyProductCampaignLoading()
    {
        $productNumbers = [];
        $productNumbers[] = 123;
        $productNumbers[] = 456; // should be ignored
        $this->adapter->setProductNumbers($productNumbers);
        $this->adapter->setIdsOnly(true);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertNull($products[0]->getField('Brand'));

        $this->adapter->setIdsOnly(false);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertEquals(
            'KHE',
            $products[0]->getField('Brand'),
            'not full record details loaded after switching to idsOnly=false'
        );
    }

    public function testShoppingCartCampaignLoading()
    {
        $productNumbers = [];
        $productNumbers[] = 456;
        $productNumbers[] = 789;
        $this->adapter->makeShoppingCartCampaign();
        $this->adapter->setProductNumbers($productNumbers);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertInstanceOf('FACTFinder\Data\Campaign', $campaigns[0]);

        $this->assertTrue($campaigns->hasRedirect());
        $this->assertEquals('http://www.fact-finder.de', $campaigns->getRedirectUrl());

        $this->assertTrue($campaigns->hasFeedback());
        $expectedFeedback = "test feedback";
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('html header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('9'));

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertEquals('KHE', $products[0]->getField('Brand'));

        $this->assertFalse($campaigns->hasActiveQuestions());
    }

    public function testPageCampaignLoading()
    {
        $pageId = "123";
        $this->adapter->makePageCampaign();
        $this->adapter->setPageId($pageId);
        $campaigns = $this->adapter->getCampaigns();

        $this->assertInstanceOf('FACTFinder\Data\CampaignIterator', $campaigns);
        $this->assertInstanceOf('FACTFinder\Data\Campaign', $campaigns[0]);

        $this->assertTrue($campaigns->hasRedirect());
        $this->assertEquals('http://www.fact-finder.de', $campaigns->getRedirectUrl());

        $this->assertTrue($campaigns->hasFeedback());
        $expectedFeedback = "test feedback";
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('html header'));
        $this->assertEquals($expectedFeedback, $campaigns->getFeedback('9'));

        $this->assertTrue($campaigns->hasPushedProducts());
        $products = $campaigns->getPushedProducts();
        $this->assertEquals(1, count($products));
        $this->assertEquals('221910', $products[0]->getId());
        $this->assertEquals('KHE', $products[0]->getField('Brand'));

        $this->assertFalse($campaigns->hasActiveQuestions());
    }
}
