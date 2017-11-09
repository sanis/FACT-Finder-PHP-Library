<?php

namespace FACTFinder\Test\Data;

use FACTFinder\Data\SearchParameters;
use FACTFinder\Util\Parameters;

class SearchParametersTest extends \FACTFinder\Test\BaseTestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function testConstructionFromParameters()
    {
        $parameters = new Parameters();
        $parameters['query'] = 'bmx';
        $parameters['seoPath'] = '/bmx-bike/q';
        $parameters['channel'] = 'de';
        $parameters['advisorStatus'] = '2-_0_0';
        $parameters['productsPerPage'] = 12;
        $parameters['filterBrand'] = 'KHE';
        $parameters['filterColor'] = 'green';
        $parameters['sortPrice'] = 'asc';
        $parameters['catalog'] = 'true';
        $parameters['followSearch'] = '9832';

        $searchParameters = new SearchParameters($parameters);

        $this->assertEquals('bmx', $searchParameters->getQuery());
        $this->assertEquals('/bmx-bike/q', $searchParameters->getSeoPath());
        $this->assertEquals('de', $searchParameters->getChannel());
        $this->assertEquals('2-_0_0', $searchParameters->getAdvisorStatus());
        $this->assertEquals(12, $searchParameters->getProductsPerPage());
        $this->assertEquals(1, $searchParameters->getCurrentPage());
        $this->assertEquals(9832, $searchParameters->getFollowSearch());

        $this->assertEquals(
            ['Brand' => 'KHE', 'Color' => 'green'],
            $searchParameters->getFilters()
        );
        $this->assertEquals(
            ['Price' => 'asc'],
            $searchParameters->getSortings()
        );

        $this->assertTrue($searchParameters->isNavigationEnabled());
    }
}
