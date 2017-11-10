<?php

namespace FACTFinder\Adapter;

use FACTFinder\Data\SuggestQuery;

/**
 * TODO: Are there any other FF 6.8 features left which we are not making use of
 *       yet? If so: change that.
 */
class Suggest extends AbstractAdapter
{
    /**
     * @var \FACTFinder\Data\SuggestQuery[]
     */
    private $suggestions;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($configuration, $request, $urlBuilder, $encodingConverter);

        $this->request->setAction('Suggest.ff');

        $this->request->setConnectTimeout($configuration->getSuggestConnectTimeout());
        $this->request->setTimeout($configuration->getSuggestTimeout());
    }

    /**
     * Get the suggestions from FACT-Finder as an array of SuggestQuery's.
     *
     * @return \FACTFinder\Data\SuggestQuery[]
     */
    public function getSuggestions()
    {
        if (null === $this->suggestions) {
            $this->suggestions = $this->createSuggestions();
        }

        return $this->suggestions;
    }

    /**
     * Get the suggestions from FACT-Finder as the string returned by the
     * server.
     *
     * @param string $format   Optional. Either 'json' or 'jsonp'. Use to
     *                         overwrite the 'format' parameter.
     * @param string $callback Optional name to overwrite the 'callback'
     *                         parameter, which determines the name of the
     *                         callback the response is wrapped in.
     *
     * @return string
     */
    public function getRawSuggestions($format = null, $callback = null)
    {
        $this->usePassthroughResponseContentProcessor();

        if (null !== $format) {
            $this->parameters['format'] = $format;
        }
        if (null !== $callback) {
            $this->parameters['callback'] = $callback;
        }

        return $this->getResponseContent();
    }

    private function createSuggestions()
    {
        $suggestions = [];

        $this->useJsonResponseContentProcessor();

        if (isset($this->parameters['format'])) {
            $oldFormat = $this->parameters['format'];
        }

        $this->parameters['format'] = 'json';
        $suggestData = $this->getResponseContent();
        if (parent::isValidResponse($suggestData)) {
            if (isset($suggestData['suggestions'])) {
                $suggestData = $suggestData['suggestions'];
            }

            foreach ($suggestData as $suggestQueryData) {
                $suggestLink = $this->convertServerQueryToClientUrl($suggestQueryData['searchParams']);

                $suggestAttributes = [];
                if (isset($suggestQueryData['attributes']) && is_array($suggestQueryData['attributes'])) {
                    $suggestAttributes = $suggestQueryData['attributes'];
                }

                $suggestions[] = new SuggestQuery(
                    $suggestQueryData['name'],
                    $suggestLink,
                    $suggestQueryData['hitCount'],
                    $suggestQueryData['type'],
                    $suggestQueryData['image'],
                    $suggestAttributes
                );
            }
        }

        if (isset($oldFormat)) {
            $this->parameters['format'] = $oldFormat;
        }

        return $suggestions;
    }
}
