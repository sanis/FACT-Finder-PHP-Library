<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\ConnectionData;
use FACTFinder\Core\Server\FileSystemDataProvider;
use FACTFinder\Core\Server\Request;

class RequestTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;
    /**
     * @var FACTFinder\Core\Server\Request
     */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration(static::class);

        $dataProvider = new FileSystemDataProvider(
            $configuration
        );
        $dataProvider->setFileLocation(RESOURCES_DIR . DS . 'responses');

        $this->request = new Request(new ConnectionData(), $dataProvider);
        $this->configuration = $configuration;
    }

    public function testGetResponse()
    {
        $this->configuration->makeHttpAuthenticationType();

        $parameters = $this->request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $this->request->setAction('TagCloud.ff');

        $response = $this->request->getResponse();
        $expectedContent = file_get_contents(
            RESOURCES_DIR . DS
            . 'responses' . DS
            . 'TagCloud.86b6b33590e092674009abfe3d7fc170.json'
        );
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals($expectedContent, $response->getContent());
    }

    public function testResetLoaded()
    {
        //setup first request
        $this->configuration->makeHttpAuthenticationType();

        $parameters = $this->request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $this->request->setAction('TagCloud.ff');

        $response = $this->request->getResponse();
        $expectedContent = file_get_contents(
            RESOURCES_DIR . DS
            . 'responses' . DS
            . 'TagCloud.86b6b33590e092674009abfe3d7fc170.json'
        );
        $this->assertEquals($expectedContent, $response->getContent());

        //setup second request without changing parameters
        $this->request->resetLoaded();
        $response2 = $this->request->getResponse();
        //should not be reloaded as url/parameters did not change
        $this->assertSame($response, $response2);

        //setup third request with changed parameters
        $this->request->resetLoaded();
        $parameters['wordCount'] = '3';
        $response2 = $this->request->getResponse();
        //should be reloaded as url/parameters did change
        $this->assertNotSame($response, $response2);
    }
}
