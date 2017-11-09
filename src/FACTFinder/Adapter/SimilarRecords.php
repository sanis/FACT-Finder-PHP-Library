<?php

namespace FACTFinder\Adapter;

use FACTFinder\Data\Record;
use FACTFinder\Data\Result;

class SimilarRecords extends ConfigurableResponse
{
    /**
     * @var mixed[]
     * @see getSimilarAttributes()
     */
    private $similarAttributes;

    /**
     * @var FACTFinder\Data\Result
     */
    private $similarRecords;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($configuration, $request, $urlBuilder, $encodingConverter);

        $this->request->setAction('SimilarRecords.ff');
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }


    /**
     * Set the maximum amount of similar records to be fetched.
     *
     * @param int $recordCount The number of records to be fetched. Something
     *                         else than a positive integer is passed, the record count will be
     *                         unlimited (or determined by FACT-Finder).
     */
    public function setRecordCount($recordCount)
    {
        $parameters = $this->request->getParameters();
        if (is_numeric($recordCount) && (int)$recordCount == (float)$recordCount && $recordCount > 0) {
            $parameters['maxRecordCount'] = $recordCount;
        } else {
            unset($parameters['maxRecordCount']);
        }
        // Make sure that the records are fetched again. In principle, we only
        // have to do this when recordCount increases.
        $this->upToDate = false;
    }

    /**
     * Set one product IDs to get similar records for.
     *
     * @param string $productID
     */
    public function setProductID($productID)
    {
        $parameters = $this->request->getParameters();
        $parameters['id'] = $productID;
        $this->upToDate = false;
    }

    /**
     * Returns the attributes based on which similar records are determined as
     * well as the source product's values of those attributes. If no ID has
     * been set, there will be a warning raised and an empty array will be
     * returned.
     *
     * @return mixed[] Attribute names as keys and attribute values as values.
     */
    public function getSimilarAttributes()
    {
        if (null === $this->similarAttributes || !$this->upToDate) {
            $this->similarAttributes = $this->createSimilarAttributes();
            $this->upToDate = true;
        }

        return $this->similarAttributes;
    }

    /**
     * Returns similar records for the ID previously specified. If no ID has
     * been set, there will be a warning raised and an empty result will be
     * returned.
     *
     * @return \FACTFinder\Data\Result
     */
    public function getSimilarRecords()
    {
        if (null === $this->similarRecords || !$this->upToDate) {
            $this->request->resetLoaded();
            $this->similarRecords = $this->createSimilarRecords();
            $this->upToDate = true;
        }

        return $this->similarRecords;
    }

    private function createSimilarAttributes()
    {
        $attributes = [];

        $parameters = $this->request->getParameters();
        if (!isset($parameters['id'])) {
            $this->logger && $this->logger->warning(
                'Similar attributes cannot be loaded without a product ID. '
                . 'Use setProductID() first.'
            );
        } else {
            $jsonData = $this->getResponseContent();
            if (parent::isValidResponse($jsonData)) {
                foreach ($jsonData['attributes'] as $attributeData) {
                    $attributes[$attributeData['name']] = $attributeData['value'];
                }
            }
        }

        return $attributes;
    }

    private function createSimilarRecords()
    {
        $records = [];

        $parameters = $this->request->getParameters();
        if (!isset($parameters['id'])) {
            $this->logger && $this->logger->warning(
                'Similar records cannot be loaded without a product ID. '
                . 'Use setProductID() first.'
            );
        } else {
            $position = 1;
            $jsonData = $this->getResponseContent();
            if (parent::isValidResponse($jsonData)) {
                foreach ($jsonData['records'] as $recordData) {
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
