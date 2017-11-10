<?php

namespace FACTFinder\Adapter;

use FACTFinder\Data\TagQuery;
use FACTFinder\Util\Parameters;

class TagCloud extends AbstractAdapter
{
    /**
     * @var TagQuery[]
     */
    private $tagCloud;

    /**
     * @var string
     */
    private $lastRequestQuery;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($configuration, $request, $urlBuilder, $encodingConverter);

        $this->request->setAction('TagCloud.ff');
        $this->parameters['do'] = 'getTagCloud';
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }

    /**
     * Get the tag cloud from FACT-Finder as an array of TagQuery's.
     *
     * @param
     *
     * @return \FACTFinder\Data\TagQuery[]
     */
    public function getTagCloud($requestQuery = null)
    {
        if (null === $this->tagCloud || $requestQuery != $this->lastRequestQuery) {
            $this->tagCloud = $this->createTagCloud($requestQuery);
            $this->lastRequestQuery = $requestQuery;
        }

        return $this->tagCloud;
    }

    /**
     * Set the maximum amount of tag queries to be fetched.
     *
     * @param int $wordCount The number of tag queries to be fetched. Something
     *                       else than a positive integer is passed, the word count will be
     *                       unlimited (or determined by FACT-Finder).
     */
    public function setWordCount($wordCount)
    {
        $parameters = $this->request->getParameters();
        if (is_numeric($wordCount) && (int)$wordCount == (float)$wordCount && $wordCount > 0) {
            $parameters['wordCount'] = $wordCount;
        } else {
            unset($parameters['wordCount']);
        }
        // Make sure that the tag cloud is fetched again. In theory, we only
        // have to do this when wordCount increases.
        $this->tagCloud = null;
    }

    private function createTagCloud($requestQuery = null)
    {
        $tagCloud = [];

        $tagCloudData = $this->getResponseContent();
        if (parent::isValidResponse($tagCloudData)) {
            foreach ($tagCloudData as $tagQueryData) {
                $query = $tagQueryData['query'];

                // TODO: Once JIRA issue FF-5328 is fixed, retrieve the
                //       parameters from searchParams, like all other adapters
                //       do.

                $parameters = new Parameters();
                $parameters['query'] = $query;

                $tagCloud[] = new TagQuery(
                    $query,
                    $this->urlBuilder->generateUrl($parameters),
                    $requestQuery == $query,
                    $tagQueryData['weight'],
                    $tagQueryData['searchCount']
                );
            }
        }

        return $tagCloud;
    }
}
