<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\ConnectionData;
use FACTFinder\Core\Server\FileSystemDataProvider;

class FileSystemDataProviderTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var FACTFinder\Core\Server\FileSystemDataProvider
     */
    protected $dataProvider;

    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration(static::class);

        $this->dataProvider = new FileSystemDataProvider($configuration);
        $this->configuration = $configuration;
    }

    public function testLoadResponse()
    {
        $this->dataProvider->setFileLocation(RESOURCES_DIR . DS . 'responses');
        $this->configuration->makeHttpAuthenticationType();

        $connectionData = new ConnectionData();
        $id = $this->dataProvider->register($connectionData);

        $parameters = $connectionData->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $connectionData->setAction('TagCloud.ff');

        $this->dataProvider->loadResponse($id);

        $response = $connectionData->getResponse();
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
}
