<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\ConnectionData;
use FACTFinder\Core\Server\EasyCurlDataProvider;

class EasyCurlDataProviderTest extends \FACTFinder\Test\BaseTestCase
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
     * @var FACTFinder\Core\Server\EasyCurlDataProvider
     */
    protected $dataProvider;

    public function setUp()
    {
        parent::setUp();

        $curlStub = $this->getCurlStub();
        $configuration = $this->getConfiguration(static::class);
        $serverUrlBuilder = $this->getServerUrlBuilder($configuration);

        $this->dataProvider = new EasyCurlDataProvider(
            $configuration,
            $curlStub,
            $serverUrlBuilder
        );

        $this->configuration = $configuration;
        $this->curlStub = $curlStub;
    }

    public function testLoadResponse()
    {
        $this->configuration->makeHttpAuthenticationType();

        $requiredOptions = [
            CURLOPT_URL => 'http://user:userpw@demoshop.fact-finder.de:80/FACT-Finder/TagCloud.ff?format=json&do=getTagCloud&verbose=true&channel=de',
        ];
        $responseContent = 'test response';
        $info = [
            CURLINFO_HTTP_CODE => '200',
        ];

        $this->curlStub->setResponse($responseContent, $requiredOptions);
        $this->curlStub->setInformation($info, $requiredOptions);

        $connectionData = new ConnectionData();
        $id = $this->dataProvider->register($connectionData);

        $parameters = $connectionData->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $connectionData->setAction('TagCloud.ff');

        $this->dataProvider->loadResponse($id);

        $response = $connectionData->getResponse();
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($responseContent, $response->getContent());
    }
}
