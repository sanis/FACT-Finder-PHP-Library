<?php

namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * This implementation backs the Request with a FileSystemDataProvider.
 */
class FileSystemRequestFactory implements RequestFactoryInterface
{
    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;
    /**
     * @var FileSystemDataProvider
     */
    private $dataProvider;

    /**
     * @var \FACTFinder\Util\Parameters
     */
    private $requestParameters;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Util\Parameters $requestParameters
    ) {
        $this->configuration = $configuration;

        $this->dataProvider = FF::getInstance('Core\Server\FileSystemDataProvider', $configuration);

        $this->requestParameters = $requestParameters;
    }

    public function setFileLocation($path)
    {
        $this->dataProvider->setFileLocation($path);
    }

    /**
     * Returns a request object all wired up and ready for use.
     *
     * @return Request
     */
    public function getRequest()
    {
        $connectionData = FF::getInstance('Core\Server\ConnectionData', clone $this->requestParameters);

        return FF::getInstance('Core\Server\Request', $connectionData, $this->dataProvider);
    }
}
