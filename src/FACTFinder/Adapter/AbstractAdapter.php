<?php

namespace FACTFinder\Adapter;

use FACTFinder\Util\Parameters;
use Psr\Log\LoggerAwareTrait;

/**
 * Base class for all adapters. An adapter is a class that configures a request
 * to some FACT-Finder action and transforms the result into useful domain
 * objects (usually objects of classes from the \Data namespace).
 * The adapter classes could conceivably be placed in the \Core\Server
 * namespace, but that would potentially discourage fiddling with and extending
 * these classes. The adapters are main components of the external API of this
 * library. Most other classes are just used to make the adapters work.
 */
abstract class AbstractAdapter
{
    use LoggerAwareTrait;

    /**
     * @var \FACTFinder\Core\ConfigurationInterface
     */
    protected $configuration;
    /**
     * @var \FACTFinder\Core\Server\Request
     */
    protected $request;
    /**
     * @var \FACTFinder\Util\Parameters
     */
    protected $parameters;
    /**
     * @var \FACTFinder\Core\Client\UrlBuilder
     */
    protected $urlBuilder;
    /**
     * @var bool
     */
    protected $upToDate = false;

    /**
     * @var callable
     */
    protected $responseContentProcessor;

    /**
     * @var \FACTFinder\Core\Server\Response
     */
    protected $lastResponse;

    /**
     * @var object The processed response content.
     */
    protected $responseContent;

    /**
     * @var string The last error message.
     */
    private $error;

    /**
     * @var string The last stack trace.
     */
    private $stackTrace;

    /**
     * @var \FACTFinder\Core\AbstractEncodingConverter
     */
    private $encodingConverter;

    /**
     * @param \FACTFinder\Core\ConfigurationInterface    $configuration
     *                                                             Configuration object to use.
     * @param \FACTFinder\Core\Server\Request            $request  The request object from
     *                                                             which to obtain the server data.
     * @param \FACTFinder\Core\Client\UrlBuilder         $urlBuilder
     *                                                             Client URL builder object to use.
     * @param \FACTFinder\Core\AbstractEncodingConverter $encodingConverter
     *                                                             Encoding converter object to use
     */
    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        $this->configuration = $configuration;
        $this->request = $request;
        $this->parameters = $request->getParameters();
        $this->urlBuilder = $urlBuilder;
        $this->encodingConverter = $encodingConverter;

        $this->usePassthroughResponseContentProcessor();
    }

    /**
     * Returns a message if an error occurred.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns the stack trace if an error occurred.
     *
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

    protected function usePassthroughResponseContentProcessor()
    {
        $this->responseContentProcessor = function ($string) {
            return $string;
        };
    }

    protected function useJsonResponseContentProcessor()
    {
        $this->responseContentProcessor = function ($string) {

            if (strpos($string, 'Infinity') !== false) {
                $string = str_replace('Infinity', '"0"', $string);
            }

            // The second parameter turns objects into associative arrays.
            // stdClass objects don't really have any advantages over plain
            // arrays but miss out on some of the built-in array functions.
            $jsonData = json_decode($string, true);
            if (null === $jsonData) {
                throw new \InvalidArgumentException(
                    'json_decode() raised an error: ' . json_last_error()
                );
            }
            if (is_array($jsonData) && isset($jsonData['error'])) {
                $this->error = strip_tags($jsonData['error']);
                $this->logger && $this->logger->error('FACT-Finder returned error: ' . $this->error);
                if (isset($jsonData['stacktrace'])) {
                    $this->stackTrace = $jsonData['stacktrace'];
                    $this->logger && $this->logger->error("Stacktrace:\n" . $this->stackTrace);
                }
            }
            return $jsonData;
        };
    }

    protected function useXmlResponseContentProcessor()
    {
        $this->responseContentProcessor = function ($string) {
            libxml_use_internal_errors(true);
            // The constructor throws an exception on error
            $response = new \SimpleXMLElement($string);
            if (isset($response->error)) {
                $this->error = strip_tags($response->error);
                $this->logger && $this->logger->error('FACT-Finder returned error: ' . $this->error);
                if (isset($response->stacktrace)) {
                    $this->stackTrace = $response->stacktrace;
                    $this->logger && $this->logger->error("Stacktrace:\n" . $this->stackTrace);
                }
            }
            return $response;
        };
    }

    /**
     * Pass in a function to process the response content. This method is not
     * used within the library, but may be convenient when writing custom
     * adapters.
     *
     * @param object $callable A function (or invokable object) that processes
     *                         a single string parameter.
     *
     * @throws \InvalidArgumentException if $callable is not callable.
     */
    protected function useResponseContentProcessor($callable)
    {
        // Check shamelessly stolen from Pimple.php
        if (!method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Content processor is neither a Closure or invokable object.');
        }

        $this->responseContentProcessor = $callable;

        // Invalidate processed response content
        $this->responseContent = null;
    }

    protected function getResponseContent()
    {
        $response = $this->request->getResponse();

        // Only reprocess the response content, if the response is new.
        if (null === $this->responseContent
            || $response !== $this->lastResponse
        ) {
            $content = $response->getContent();

            // PHP does not (yet?) support $this->method($args) for callable
            // properties

            if ($content !== null) {
                $this->responseContent = $this->responseContentProcessor->__invoke($content);
                if ($this->encodingConverter != null) {
                    $this->responseContent = $this->encodingConverter->encodeContentForPage($this->responseContent);
                }
            } else {
                $this->responseContent = [];
            }

            $this->lastResponse = $response;
        }

        return $this->responseContent;
    }

    protected function convertServerQueryToClientUrl($query)
    {
        $parameters = new Parameters($query, true);

        return $this->urlBuilder->generateUrl($parameters);
    }

    /**
     * Returns true if the response is valid or false if an error occurred.
     *
     * @param $jsonData
     *
     * @return bool
     */
    protected function isValidResponse($jsonData)
    {
        return (!empty($jsonData) && !isset($jsonData['error']));
    }
}
