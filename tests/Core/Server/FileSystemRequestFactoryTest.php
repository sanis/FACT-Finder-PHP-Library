<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\FileSystemRequestFactory;
use FACTFinder\Util\Parameters;

class FileSystemRequestFactoryTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var FACTFinder\Core\Server\FileSystemRequestFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration(static::class);

        $this->factory = new FileSystemRequestFactory($configuration, new Parameters());
        $this->configuration = $configuration;
    }

    public function testGetWorkingRequest()
    {
        $this->factory->setFileLocation(RESOURCES_DIR . DS . 'responses');
        $this->configuration->makeHttpAuthenticationType();

        $request = $this->factory->getRequest();

        $parameters = $request->getParameters();

        $parameters['format'] = 'json';
        $parameters['do'] = 'getTagCloud';

        $request->setAction('TagCloud.ff');

        $response = $request->getResponse();
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
