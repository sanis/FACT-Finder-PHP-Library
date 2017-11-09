<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\EasyCurlRequestFactory;
use FACTFinder\Util\Parameters;

class EasyCurlRequestFactoryTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var FACTFinder\Util\CurlStub
     */
    protected $curlStub;

    /**
     * @var FACTFinder\Core\Server\EasyCurlRequestFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $curlStub = $this->getCurlStub();
        $configuration = $this->getConfiguration(static::class);

        $this->factory = new EasyCurlRequestFactory(
            $configuration,
            new Parameters(['query' => 'bmx']),
            $curlStub
        );

        $this->configuration = $configuration;
        $this->curlStub = $curlStub;
    }

    public function testGetWorkingRequest()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions = [
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?query=bmx&format=json&do=getTagCloud&verbose=true&channel=de',
        ];
        $responseContent = 'test response';
        $info = [
            CURLINFO_HTTP_CODE => '200',
        ];

        $this->curlStub->setResponse($responseContent, $requiredOptions);
        $this->curlStub->setInformation($info, $requiredOptions);

        $request = $this->factory->getRequest();

        $parameters = $request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $request->setAction('TagCloud.ff');

        $response = $request->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent, $response->getContent());
    }
}
