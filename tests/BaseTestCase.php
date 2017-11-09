<?php

namespace FACTFinder\Test;

use FACTFinder\Core\AbstractEncodingConverter;
use FACTFinder\Core\ArrayConfiguration;
use FACTFinder\Core\Client\RequestParser;
use FACTFinder\Core\ConfigurationInterface;
use FACTFinder\Core\IConvEncodingConverter;
use FACTFinder\Core\Server\AbstractDataProvider;
use FACTFinder\Core\Server\FileSystemDataProvider;
use FACTFinder\Core\Server\FileSystemRequestFactory;
use FACTFinder\Core\Server\Request;
use FACTFinder\Core\Server\UrlBuilder;
use FACTFinder\Core\Utf8EncodingConverter;
use FACTFinder\Core\XmlConfiguration;
use FACTFinder\Util\CurlStub;
use Psr\Log\NullLogger;

/**
 * This is named BaseTestCASE so that PHPUnit does not look for tests inside
 * this class.
 *
 * @package default
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return NullLogger
     */
    public function getLogger()
    {
        return new NullLogger();
    }

    /**
     * @param $class
     *
     * @return ArrayConfiguration|XmlConfiguration
     */
    public function getConfiguration($class)
    {
        if (strpos($class, 'ArrayConfiguration')) {
            $config = include RESOURCES_DIR . DS . 'config.php';
            return new ArrayConfiguration($config, 'test');
        } else {
            return new XmlConfiguration(RESOURCES_DIR . DS . 'config.xml', 'test');
        }
    }

    /**
     * @param ConfigurationInterface $configuration
     *
     * @return AbstractEncodingConverter
     */
    public function getConverter($configuration)
    {
        if (extension_loaded('iconv')) {
            $type = IConvEncodingConverter::class;
        } elseif (function_exists('utf8_encode') && function_exists('utf8_decode')) {
            $type = Utf8EncodingConverter::class;
        } else {
            return;
        }
        //TODO: Skip test if no conversion method is available.
        //    $that->markTestSkipped('No encoding conversion available.');

        return new $type($configuration);
    }

    /**
     * @param ConfigurationInterface $configuration
     *
     * @return UrlBuilder
     */
    public function getServerUrlBuilder($configuration)
    {
        return new UrlBuilder($configuration);
    }

    /**
     * @param ConfigurationInterface    $configuration
     * @param RequestParser             $requestParser
     * @param AbstractEncodingConverter $encodingConverter
     *
     * @return \FACTFinder\Core\Client\UrlBuilder
     */
    public function getClientUrlBuilder($configuration, $requestParser, $encodingConverter)
    {
        return new \FACTFinder\Core\Client\UrlBuilder($configuration, $requestParser, $encodingConverter);
    }

    /**
     * @return CurlStub
     */
    public function getCurlStub()
    {
        return new CurlStub();
    }

    /**
     * @param ConfigurationInterface $configuration
     *
     * @return AbstractDataProvider
     */
    public function getDataProvider($configuration)
    {
        $dataProvider = new FileSystemDataProvider($configuration);
        $dataProvider->setFileLocation(RESOURCES_DIR . DS . 'responses');
        return $dataProvider;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param RequestParser          $requestParser
     *
     * @return FileSystemRequestFactory
     */
    public function getRequestFactory($configuration, $requestParser)
    {
        $requestFactory = new FileSystemRequestFactory($configuration, $requestParser->getRequestParameters());
        $requestFactory->setFileLocation(RESOURCES_DIR . DS . 'responses');
        return $requestFactory;
    }

    /**
     * @param FileSystemRequestFactory $requestFactory
     *
     * @return Request
     */
    public function getRequest($requestFactory)
    {
        return $requestFactory->getRequest();
    }

    /**
     * @param ConfigurationInterface    $configuration
     * @param AbstractEncodingConverter $encodingConverter
     *
     * @return RequestParser
     */
    public function getRequestParser($configuration, $encodingConverter)
    {
        return new RequestParser($configuration, $encodingConverter);
    }
}
