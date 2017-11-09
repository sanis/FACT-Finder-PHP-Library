<?php

namespace FACTFinder\Core\Server;

use FACTFinder\Util\Curl;

/**
 * This implementation backs the Request with a MultiCurlDataProvider.
 */
class MultiCurlRequestFactory implements RequestFactoryInterface
{
    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;
    /**
     * @var MultiCurlDataProvider
     */
    private $dataProvider;

    /**
     * @var \FACTFinder\Util\Parameters
     */
    private $requestParameters;

    /**
     * @param \FACTFinder\Core\ConfigurationInterface $configuration
     * @param \FACTFinder\Util\Parameters             $requestParameters
     * @param \FACTFinder\Util\CurlInterface          $curl Optional. If omitted, an
     *                                                      instance of \FACTFinder\Util\Curl will be used.
     */
    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Util\Parameters $requestParameters,
        \FACTFinder\Util\CurlInterface $curl = null
    ) {
        $this->configuration = $configuration;

        $urlBuilder = new UrlBuilder($configuration);

        $this->dataProvider = new MultiCurlDataProvider(
            $configuration,
            null === $curl ? new Curl() : $curl,
            $urlBuilder
        );

        $this->requestParameters = $requestParameters;
    }

    /**
     * Returns a request object all wired up and ready for use.
     *
     * @return Request
     */
    public function getRequest()
    {
        $connectionData = new ConnectionData(clone $this->requestParameters);
        return new Request($connectionData, $this->dataProvider);
    }
}
