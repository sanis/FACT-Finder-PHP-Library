<?php

namespace FACTFinder\Core\Client;

use FACTFinder\Core\AbstractEncodingConverter;
use FACTFinder\Core\ParametersConverter;
use FACTFinder\Loader as FF;
use FACTFinder\Util\Parameters;
use Psr\Log\LoggerAwareTrait;

/**
 * Generates URLs to be used in requests to the client.
 */
class UrlBuilder
{
    use LoggerAwareTrait;

    /**
     * @var ParametersConverter
     */
    private $parametersConverter;

    /**
     * @var RequestParser
     */
    private $requestParser;

    /**
     * @var AbstractEncodingConverter
     */
    private $encodingConverter;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Client\RequestParser $requestParser,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        $this->parametersConverter = FF::getInstance('Core\ParametersConverter', $configuration);
        $this->requestParser = $requestParser;
        $this->encodingConverter = $encodingConverter;
    }

    /**
     * Generates a link to be used on the page that leads to the client from a
     * set of server parameters. Note that the link will still be UTF-8 encoded.
     * If the page uses a different encoding, conversion to that encoding has to
     * be done when actually rendering the string to the page.
     *
     * TODO: Should the signature be more similar to that of \Server\UrlBuilder?
     *
     * @param Parameters $parameters The server parameters that
     *                                               should be retrieved when the link is followed.
     * @param string     $target An optional request target. If omitted, the target
     *                                               of the current request will be used. For instance, this parameter
     *                                               can be used if a product detail page needs a different target.
     *
     * @return string
     */
    public function generateUrl($parameters, $target = null)
    {
        $parameters = $this->parametersConverter->convertServerToClientParameters($parameters);

        $parameters = $this->encodingConverter != null
            ? $this->encodingConverter->encodeClientUrlData($parameters) : $parameters;

        if (!is_string($target)) {
            $target = $this->requestParser->getRequestTarget();
        }

        if ($parameters->offsetExists('seoPath')) {
            $seoPath = $parameters['seoPath'];
            $parameters->offsetUnset('seoPath');
            $seoPathPosition = strrpos($target, '/s/');
            if ($seoPathPosition > -1) {
                $target = substr($target, 0, $seoPathPosition);
            }
            $url = rtrim($target, '/') . '/s' . urldecode($seoPath) . '?' . $parameters->toPhpQueryString();
        } else {
            $url = $target . '?' . $parameters->toPhpQueryString();
        }
        return $url;
    }
}
