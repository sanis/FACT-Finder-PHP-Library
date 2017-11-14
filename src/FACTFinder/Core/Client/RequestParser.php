<?php

namespace FACTFinder\Core\Client;

use FACTFinder\Core\AbstractEncodingConverter;
use FACTFinder\Core\ConfigurationInterface;
use FACTFinder\Core\ParametersConverter;
use FACTFinder\Util\Parameters;
use Psr\Log\LoggerAwareTrait;

/**
 * Extracts several data from the request made to the client.
 */
class RequestParser
{
    use LoggerAwareTrait;

    protected $clientRequestParameters;
    protected $serverRequestParameters;
    protected $requestTarget;
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;
    /**
     * @var AbstractEncodingConverter
     */
    protected $encodingConverter;
    /**
     * @var ParametersConverter
     */
    protected $parametersConverter;

    /**
     * @param ConfigurationInterface    $configuration
     * @param AbstractEncodingConverter $encodingConverter
     */
    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        $this->configuration = $configuration;
        $this->encodingConverter = $encodingConverter;
        $this->parametersConverter = new ParametersConverter($configuration);
    }

    /**
     * Loads parameters from the request and returns a Parameter object.
     * Also takes care of encoding conversion if necessary. Finally it will make
     * all necessary conversions according to the ignore/require/mapping
     * directives given in the configuration for the server. Use this method
     * for any other part of the library that needs the request parameters.
     *
     * @return Parameters Array of UTF-8 encoded and converted parameters
     */
    public function getRequestParameters()
    {
        if (null === $this->serverRequestParameters) {
            $clientParameters = $this->getClientRequestParameters();
            $this->serverRequestParameters =
                $this->parametersConverter->convertClientToServerParameters($clientParameters);
        }

        return $this->serverRequestParameters;
    }

    /**
     * Loads parameters from the request and returns a Parameter object.
     * Also takes care of encoding conversion if necessary. However, the
     * parameters themselves are not converted (that is ignore, require and
     * mapping directives in the configuration are not taken into account).
     *
     * You won't usually need this method unless you really want to get access
     * to some parameters that would be ignored or mapped otherwise.
     *
     * For use with any other part of the library, use getRequestParameters()
     * instead, which converts the parameters for usage with the server.
     *
     * @return Parameters Array of UTF-8 encoded parameters
     */
    public function getClientRequestParameters()
    {
        if (null === $this->clientRequestParameters) {
            if (isset($_SERVER['QUERY_STRING'])) {
                // TODO: Respect variables_order so that conflicting variables
                //       lead to the same result as in $_REQUEST (save for
                //       $_COOKIE variables). This todo also goes for the second
                //       alternative.
                $parameters = new Parameters($_SERVER['QUERY_STRING']);

                $data = $_POST;

                if (!empty($data)) {
                    foreach ($data as $key => $value) {
                        if (is_array($value)) {
                            unset($data[$key]);
                        }
                    }
                }

                $parameters->setAll($data);
            } elseif (isset($_GET)) {
                $this->logger && $this->logger->warning(
                    '$_SERVER[\'QUERY_STRING\'] is not available. '
                    . 'Using $_GET instead. This may cause problems '
                    . 'if the query string contains parameters with '
                    . 'non-[a-zA-Z0-9_] characters.'
                );

                // Don't use $_REQUEST, because it also contains $_COOKIE.
                // Note that we don't have to URL decode here, because _GET is
                // already URL decoded.

                $parameters = new Parameters(array_merge($_POST, $_GET));
            } else {
                $parameters = new Parameters();
            }

            if (isset($_SERVER['REQUEST_URI'])) {
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $seoPathPosition = strrpos($path, '/s/');
                if ($seoPathPosition > -1) {
                    $encodedSeoPath = substr($path, $seoPathPosition + 2);
                    $decodedSeoPath = urldecode($encodedSeoPath);
                    $parameters['seoPath'] = $decodedSeoPath;
                }
            }

            // Convert encoding and then the parameters themselves
            $this->clientRequestParameters =
                $this->encodingConverter != null
                    ? $this->encodingConverter->decodeClientUrlData($parameters) : $parameters;
        }

        return $this->clientRequestParameters;
    }

    /**
     * Get target of the current request.
     *
     * @return string request target
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget === null) {
            // Workaround for some servers (IIS) which do not provide
            // $_SERVER['REQUEST_URI']. Taken from
            // http://php.net/manual/en/reserved.variables.server.php#108186
            if (!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
                if (isset($_SERVER['QUERY_STRING'])) {
                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            if (strpos($_SERVER['REQUEST_URI'], '?') === false) {
                $this->requestTarget = $_SERVER['REQUEST_URI'];
            } else {
                $parts = explode('?', $_SERVER['REQUEST_URI']);
                $this->requestTarget = $parts[0];
            }

            $seoPathPosition = strrpos($this->requestTarget, '/s/');
            if ($seoPathPosition > -1) {
                $this->requestTarget = substr($this->requestTarget, 0, $seoPathPosition);
            }

            // Use rawurldecode() so that +'s are not converted to spaces.
            $this->requestTarget = rawurldecode($this->requestTarget);
            if ($this->encodingConverter != null) {
                $this->requestTarget = $this->encodingConverter->decodeClientUrlData($this->requestTarget);
            }
        }
        return $this->requestTarget;
    }
}
