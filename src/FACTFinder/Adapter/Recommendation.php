<?php

namespace FACTFinder\Adapter;

use FACTFinder\Data\Record;
use FACTFinder\Data\Result;

class Recommendation extends PersonalisedResponse
{
    /**
     * @var FACTFinder\Data\Result
     */
    private $recommendations;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($configuration, $request, $urlBuilder, $encodingConverter);

        $this->request->setAction('Recommender.ff');
        $this->parameters['do'] = 'getRecommendation';
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }


    /**
     * Set the maximum amount of recommendations to be fetched.
     *
     * @param int $recordCount The number of records to be fetched. Something
     *                         else than a positive integer is passed, the record count will be
     *                         unlimited (or determined by FACT-Finder).
     */
    public function setRecordCount($recordCount)
    {
        $parameters = $this->request->getParameters();
        if (is_numeric($recordCount) && $recordCount > 0 && (int)$recordCount == (float)$recordCount) {
            $parameters['maxResults'] = $recordCount;
        } else {
            unset($parameters['maxResults']);
        }
        // Make sure that the recommendations are fetched again. In theory,
        // we only have to do this when recordCount increases.
        $this->upToDate = false;
    }

    /**
     * Set one or multiple product IDs to base recommendation on, overwriting
     * any IDs previously set.
     *
     * @param string|string[] $productIDs One or more product IDs.
     */
    public function setProductIDs($productIDs)
    {
        $parameters = $this->request->getParameters();
        $parameters['id'] = $productIDs;
        $this->upToDate = false;
    }

    /**
     * Add one or multiple product IDs to base recommendation on, in addition to
     * any IDs previously set.
     *
     * @param string|string[] $productIDs One or more product IDs.
     */
    public function addProductIDs($productIDs)
    {
        $parameters = $this->request->getParameters();
        $parameters->add('id', $productIDs);
        $this->upToDate = false;
    }

    /**
     * Returns recommendations for IDs previously specified. If no IDs have been
     * set, there will be a warning raised and an empty result will be returned.
     *
     * @return \FACTFinder\Data\Result
     */
    public function getRecommendations()
    {
        if (null === $this->recommendations || !$this->upToDate) {
            $this->request->resetLoaded();
            $this->recommendations = $this->createRecommendations();
            $this->upToDate = true;
        }

        return $this->recommendations;
    }

    /**
     * Get the recommendations from FACT-Finder as the string returned by the
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
    public function getRawRecommendations($format = null, $callback = null)
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

    private function createRecommendations()
    {
        $records = [];

        $parameters = $this->request->getParameters();
        if (!isset($parameters['id'])) {
            $this->logger && $this->logger->warning(
                'Recommendations cannot be loaded without a product ID. '
                . 'Use setProductIDs() or addProductIDs() first.'
            );
        } else {
            $recommenderData = $this->getResponseContent();
            if (parent::isValidResponse($recommenderData)) {
                if (isset($recommenderData['resultRecords'])) {
                    $recommenderData = $recommenderData['resultRecords'];
                }
                $position = 1;
                foreach ($recommenderData as $recordData) {
                    $records[] = $this->createRecord($recordData, $position++);
                }
            }
        }

        return new Result($records, count($records));
    }

    private function createRecord($recordData, $position)
    {
        return new Record((string)$recordData['id'], $recordData['record'], 100.0, $position);
    }
}
