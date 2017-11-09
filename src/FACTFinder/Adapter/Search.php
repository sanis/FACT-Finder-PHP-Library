<?php

namespace FACTFinder\Adapter;

use FACTFinder\Data\AdvisorAnswer;
use FACTFinder\Data\AdvisorQuestion;
use FACTFinder\Data\AfterSearchNavigation;
use FACTFinder\Data\ArticleNumberSearchStatus;
use FACTFinder\Data\BreadCrumb;
use FACTFinder\Data\BreadCrumbTrail;
use FACTFinder\Data\BreadCrumbType;
use FACTFinder\Data\Campaign;
use FACTFinder\Data\CampaignIterator;
use FACTFinder\Data\Filter;
use FACTFinder\Data\FilterGroup;
use FACTFinder\Data\FilterSelectionType;
use FACTFinder\Data\FilterStyle;
use FACTFinder\Data\FilterType;
use FACTFinder\Data\Item;
use FACTFinder\Data\Page;
use FACTFinder\Data\Paging;
use FACTFinder\Data\Record;
use FACTFinder\Data\Result;
use FACTFinder\Data\ResultsPerPageOptions;
use FACTFinder\Data\SearchParameters;
use FACTFinder\Data\SearchStatus;
use FACTFinder\Data\SingleWordSearchItem;
use FACTFinder\Data\SliderFilter;
use FACTFinder\Data\Sorting;
use FACTFinder\Data\SortingDirection;
use FACTFinder\Data\SortingItem;
use FACTFinder\Data\SortingItems;
use FACTFinder\Util\Parameters;

class Search extends PersonalisedResponse
{
    /**
     * @var Result
     */
    private $result;

    /**
     * @var SingleWordSearchItem[]
     */
    private $singleWordSearch;

    /**
     * @var AfterSearchNavigation
     */
    private $afterSearchNavigation;

    /**
     * @var ResultsPerPageOptions
     */
    private $resultsPerPageOptions;

    /**
     * @var Paging
     */
    private $paging;

    /**
     * @var Sorting
     */
    private $sorting;

    /**
     * @var SortingItems
     */
    private $sortingItems;

    /**
     * @var BreadCrumbTrail
     */
    private $breadCrumbTrail;

    /**
     * @var CampaignIterator
     */
    private $campaigns;

    public function __construct(
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct($configuration, $request, $urlBuilder, $encodingConverter);

        $this->request->setAction('Search.ff');
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }

    /**
     * Overwrite the query on the request.
     *
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->parameters['query'] = $query;
    }

    /**
     * @return \FACTFinder\Data\Result
     */
    public function getResult()
    {
        if (null === $this->result || !$this->upToDate) {
            $this->request->resetLoaded();
            $this->result = $this->createResult();
            $this->upToDate = true;
        }

        return $this->result;
    }

    /**
     * @return \FACTFinder\Data\SingleWordSearchItem[]
     */
    public function getSingleWordSearch()
    {
        if (null === $this->singleWordSearch) {
            $this->singleWordSearch = $this->createSingleWordSearch();
        }

        return $this->singleWordSearch;
    }

    /**
     * @return \FACTFinder\Data\SearchStatus
     */
    public function getStatus()
    {
        $status = SearchStatus::NoResult();

        $jsonData = $this->getResponseContent();
        if ($this->isValidResponse($jsonData)) {
            switch ($jsonData['searchResult']['resultStatus']) {
                case 'nothingFound':
                    $status = SearchStatus::EmptyResult();
                    break;
                case 'resultsFound':
                    $status = SearchStatus::RecordsFound();
                    break;
            }
        }
        return $status;
    }

    /**
     * @return \FACTFinder\Data\ArticleNumberSearchStatus
     */
    public function getArticleNumberStatus()
    {
        $status = ArticleNumberSearchStatus::IsNoArticleNumberSearch();

        $jsonData = $this->getResponseContent();
        if ($this->isValidResponse($jsonData)) {
            switch ($jsonData['searchResult']['resultArticleNumberStatus']) {
                case 'resultsFound':
                    $status = ArticleNumberSearchStatus::IsArticleNumberResultFound();
                    break;
                case 'nothingFound':
                    $status = ArticleNumberSearchStatus::IsNoArticleNumberResultFound();
                    break;
            }
        }
        return $status;
    }

    /**
     * @return bool
     */
    public function isSearchTimedOut()
    {
        $jsonData = $this->getResponseContent();
        if ($this->isValidResponse($jsonData)) {
            return $jsonData['searchResult']['timedOut'];
        }
        return true;
    }

    /**
     * @return \FACTFinder\Data\AfterSearchNavigation
     */
    public function getAfterSearchNavigation()
    {
        if (null === $this->afterSearchNavigation) {
            $this->afterSearchNavigation = $this->createAfterSearchNavigation();
        }

        return $this->afterSearchNavigation;
    }

    /**
     * @return \FACTFinder\Data\ResultsPerPageOptions
     */
    public function getResultsPerPageOptions()
    {
        if (null === $this->resultsPerPageOptions) {
            $this->resultsPerPageOptions = $this->createResultsPerPageOptions();
        }

        return $this->resultsPerPageOptions;
    }

    /**
     * @return \FACTFinder\Data\ResultsPerPageOptions
     */
    public function createResultsPerPageOptions()
    {
        $options = [];

        $defaultOption = null;
        $selectedOption = null;

        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData)) {
            $rppData = $jsonData['searchResult']['resultsPerPageList'];
            if (!empty($rppData)) {
                foreach ($rppData as $optionData) {
                    $optionLink = $this->convertServerQueryToClientUrl($optionData['searchParams']);

                    $option = new Item($optionData['value'], $optionLink, $optionData['selected']);

                    if ($optionData['default']) {
                        $defaultOption = $option;
                    }
                    if ($optionData['selected']) {
                        $selectedOption = $option;
                    }

                    $options[] = $option;
                }
            }

            return new ResultsPerPageOptions($options, $defaultOption, $selectedOption);
        }
        return null;
    }

    /**
     * @return \FACTFinder\Data\Paging
     */
    public function getPaging()
    {
        if (null === $this->paging) {
            $this->paging = $this->createPaging();
        }

        return $this->paging;
    }

    /**
     * @return \FACTFinder\Data\Sorting
     */
    public function getSorting()
    {
        if (null === $this->sorting) {
            $this->sorting = $this->createSorting();
        }

        return $this->sorting;
    }

    /**
     * @return \FACTFinder\Data\SortingItems
     */
    public function getSortingItems()
    {
        if (null === $this->sortingItems) {
            $this->sortingItems = $this->createSortingItems();
        }

        return $this->sortingItems;
    }

    /**
     * @return \FACTFinder\Data\BreadCrumbTrail
     */
    public function getBreadCrumbTrail()
    {
        if (null === $this->breadCrumbTrail) {
            $this->breadCrumbTrail = $this->createBreadCrumbTrail();
        }

        return $this->breadCrumbTrail;
    }

    /**
     * @return \FACTFinder\Data\CampaignIterator
     */
    public function getCampaigns()
    {
        if (null === $this->campaigns) {
            $this->campaigns = $this->createCampaigns();
        }

        return $this->campaigns;
    }

    /**
     * Value for parameter "followSearch" for followups on initial search like filters, pagination, ...
     * Either from search results searchParams, request parameters or from search results "simiFirstRecord".
     * Returns 0 if no parameter "followSearch" could be acquired.
     *
     * @return int
     */
    public function getFollowSearchValue()
    {
        $jsonData = $this->getResponseContent();
        //use searchParams of result if available
        if ($this->isValidResponse($jsonData) && isset($jsonData['searchResult']['searchParams'])) {
            $parameters = new Parameters($jsonData['searchResult']['searchParams']);
            //fallback to current request
        } else {
            $parameters = $this->parameters;
        }
        $searchParameters = new SearchParameters($parameters);
        $sorting = $searchParameters->getSortings();
        $followSearch = 0;
        // check if followSearch was set in request data or sent by FF in result searchParams
        if ($searchParameters->getFollowSearch() !== 0) {
            $followSearch = $searchParameters->getFollowSearch();
            // use simiFirstRecord only if result was not sorted
        } elseif (empty($sorting)) {
            $jsonData = $this->getResponseContent();
            if ($jsonData && $jsonData['searchResult'] && isset($jsonData['searchResult']['simiFirstRecord'])) {
                $followSearch = $jsonData['searchResult']['simiFirstRecord'];
            }
            //mark as not valid
        }

        return $followSearch;
    }

    /**
     * @param \FACTFinder\Data\Campaign $campaign     The campaign object to be
     *                                                filled.
     * @param mixed[]                   $campaignData An associative array corresponding to the
     *                                                JSON for that campaign.
     */
    protected function fillCampaignWithFeedback(\FACTFinder\Data\Campaign $campaign, array $campaignData)
    {
        if (!empty($campaignData['feedbackTexts'])) {
            $feedback = [];

            foreach ($campaignData['feedbackTexts'] as $feedbackData) {
                // If present, add the feedback to both the label and the ID.
                $html = $feedbackData['html'];
                $text = $feedbackData['text'];
                if (!$html) {
                    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                }

                $label = $feedbackData['label'];
                if ($label !== '') {
                    $feedback[$label] = $text;
                }

                $id = $feedbackData['id'];
                if ($id !== null) {
                    $feedback[$id] = $text;
                }
            }

            $campaign->addFeedback($feedback);
        }
    }

    /**
     * Returns true if the search response is valid or false if an error occurred.
     *
     * @param $jsonData []
     *
     * @return bool
     */
    protected function isValidResponse($jsonData)
    {
        return (!empty($jsonData) && !isset($jsonData['error']) && isset($jsonData['searchResult']));
    }

    /**
     * @return \FACTFinder\Data\Result
     */
    private function createResult()
    {
        //init default values
        $records = [];
        $resultCount = 0;

        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData)) {
            $searchResultData = $jsonData['searchResult'];

            if (!empty($searchResultData['records'])) {
                $resultCount = $searchResultData['resultCount'];

                foreach ($searchResultData['records'] as $recordData) {
                    $position = $recordData['position'];

                    $record = new Record(
                        (string)$recordData['id'],
                        $recordData['record'],
                        $recordData['searchSimilarity'],
                        $position,
                        isset($recordData['seoPath']) ? $recordData['seoPath'] : '',
                        $recordData['keywords']
                    );

                    $records[] = $record;
                }
            }
        }

        return new Result($records, $resultCount);
    }

    /**
     * @return \FACTFinder\Data\SingleWordSearchItem[]
     */
    private function createSingleWordSearch()
    {
        $singleWordSearch = [];

        $jsonData = $this->getResponseContent();
        if ($this->isValidResponse($jsonData) && !empty($jsonData['searchResult']['singleWordResults'])) {
            foreach ($jsonData['searchResult']['singleWordResults'] as $swsData) {
                $item = new SingleWordSearchItem(
                    $swsData['word'],
                    $this->convertServerQueryToClientUrl($swsData['searchParams']),
                    $swsData['recordCount']
                );

                foreach ($swsData['previewRecords'] as $recordData) {
                    $item->addPreviewRecord(
                        new Record(
                            (string)$recordData['id'],
                            $recordData['record'],
                            $recordData['searchSimilarity'],
                            $recordData['position'],
                            '',
                            $recordData['keywords']
                        )
                    );
                }

                $singleWordSearch[] = $item;
            }
        }

        return $singleWordSearch;
    }

    /**
     * @return \FACTFinder\Data\AfterSearchNavigation
     */
    private function createAfterSearchNavigation()
    {
        $jsonData = $this->getResponseContent();

        $filterGroups = [];

        if ($this->isValidResponse($jsonData) && isset($jsonData['searchResult']['groups'])) {
            foreach ($jsonData['searchResult']['groups'] as $groupData) {
                $filterGroups[] = $this->createFilterGroup($groupData);
            }
        }

        return new AfterSearchNavigation($filterGroups);
    }

    /**
     * @param mixed[] $groupData An associative array corresponding to the JSON
     *                           for a single filter group.
     *
     * @return \FACTFinder\Data\FilterGroup
     */
    private function createFilterGroup($groupData)
    {
        $elements = array_merge($groupData['selectedElements'], $groupData['elements']);

        switch ($groupData['filterStyle']) {
            case 'SLIDER':
                $filterStyle = FilterStyle::Slider();
                break;
            case 'TREE':
                $filterStyle = FilterStyle::Tree();
                break;
            case 'MULTISELECT':
                $filterStyle = FilterStyle::MultiSelect();
                break;
            default:
                $filterStyle = FilterStyle::Regular();
                break;
        }

        $filters = [];
        foreach ($elements as $filterData) {
            if ($filterStyle == FilterStyle::Slider()) {
                $filters[] = $this->createSliderFilter($filterData);
            } else {
                $filters[] = $this->createFilter($filterData);
            }
        }

        $filterSelectionType = null;
        if (isset($groupData['selectionType'])) {
            switch ($groupData['selectionType']) {
                case 'multiSelectOr':
                    $filterSelectionType = FilterSelectionType::MultiSelectOr();
                    break;
                case 'multiSelectAnd':
                    $filterSelectionType = FilterSelectionType::MultiSelectAnd();
                    break;
                case 'singleShowUnselected':
                    $filterSelectionType = FilterSelectionType::SingleShowUnselected();
                    break;
                default:
                    $filterSelectionType = FilterSelectionType::SingleHideUnselected();
                    break;
            }
        }

        $filterType = null;
        if (isset($groupData['type'])) {
            switch ($groupData['type']) {
                case 'number':
                    $filterType = FilterType::Number();
                    break;
                default:
                    $filterType = FilterType::Text();
                    break;
            }
        }

        return new FilterGroup(
            $filters,
            $groupData['name'],
            $filterStyle,
            $groupData['detailedLinks'],
            $groupData['unit'],
            $filterSelectionType,
            $filterType,
            $groupData['showPreviewImages']
        );
    }

    /**
     * @param mixed[] $filterData An associative array corresponding to the JSON
     *                            for a single filter.
     *
     * @return \FACTFinder\Data\Filter
     */
    private function createFilter(array $filterData)
    {
        $filterLink = $this->convertServerQueryToClientUrl($filterData['searchParams']);

        return new Filter(
            $filterData['name'],
            $filterLink,
            $filterData['selected'],
            $filterData['associatedFieldName'],
            $filterData['recordCount'],
            $filterData['clusterLevel'],
            $filterData['previewImageURL'] ?: ''
        );
    }

    /**
     * @param mixed[] $filterData An associative array corresponding to the JSON
     *                            for a single slider filter.
     *
     * @return \FACTFinder\Data\SliderFilter
     */
    private function createSliderFilter(array $filterData)
    {
        // For sliders, FACT-Finder appends a filter parameter without value to
        // the 'searchParams' field, which is to be filled with the selected
        // minimum and maximum like 'filterValue=min-max'.
        // We split that parameter off, and treat it separately to ensure that
        // it stays the last parameter when converted to a client URL.
        preg_match(
            '/
            (.*)            # match and capture as much of the query as possible
            [?&]filter      # match "?filter" or "&filter" literally
            ([^&=]*)        # group 2, the field name
            =(?=$|&)        # make sure there is a "=" followed by the end of
                            # the string or another parameter
            (.*)            # match the remainder of the query
            /x',
            $filterData['searchParams'],
            $matches
        );

        if (!empty($matches)) {
            $query = $matches[1] . $matches[3];
            $fieldName = $matches[2];
        } else {
            // The URL of searchParams was not as expected, propably the current filter was not
            // added as an empty parameter. Therefore no need to remove it and we can use full searchParams URL.
            $query = $filterData['searchParams'];
            $fieldName = $filterData['associatedFieldName'];
        }

        if (urldecode($fieldName) != $filterData['associatedFieldName']) {
            $this->logger && $this->logger->warning(
                'Filter parameter of slider does not correspond '
                . 'to transmitted "associatedFieldName". Parameter: '
                . "$fieldName. Field name: "
                . $filterData['associatedFieldName'] . '.'
            );
        }

        $filterLink = $this->convertServerQueryToClientUrl($query);

        return new SliderFilter(
            $filterLink,
            $fieldName,
            $filterData['absoluteMinValue'],
            $filterData['absoluteMaxValue'],
            $filterData['selectedMinValue'],
            $filterData['selectedMaxValue']
        );
    }

    /**
     * @return \FACTFinder\Data\Paging
     */
    private function createPaging()
    {
        $pages = [];

        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData)) {
            $pagingData = $jsonData['searchResult']['paging'];
            if (!empty($pagingData)) {
                $currentPage = null;
                $pageCount = $pagingData['pageCount'];

                foreach ($pagingData['pageLinks'] as $pageData) {
                    $page = $this->createPageItem($pageData);

                    if ($pageData['currentPage']) {
                        $currentPage = $page;
                    }

                    $pages[] = $page;
                }
            }

            if (!$currentPage) {
                $currentPage = new Page($pagingData['currentPage'], $pagingData['currentPage'], '#', true);
            }

            return new Paging(
                $pages,
                $pageCount,
                $currentPage,
                $this->createPageItem($pagingData['firstLink']),
                $this->createPageItem($pagingData['lastLink']),
                $this->createPageItem($pagingData['previousLink']),
                $this->createPageItem($pagingData['nextLink'])
            );
        }
        return null;
    }

    /**
     * @param mixed[] $pageData An associative array corresponding to the JSON
     *                          for a single page link.
     *
     * @return \FACTFinder\Data\Item
     */
    private function createPageItem(array $pageData = null)
    {
        if (null === $pageData) {
            return null;
        }

        $pageLink = $this->convertServerQueryToClientUrl($pageData['searchParams']);

        return new Page($pageData['number'], $pageData['caption'], $pageLink, $pageData['currentPage']);
    }

    /**
     * @return \FACTFinder\Data\Sorting
     */
    private function createSorting()
    {
        $sortOptions = [];

        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData)) {
            $sortingData = $jsonData['searchResult']['sortsList'];
            if (!empty($sortingData)) {
                foreach ($sortingData as $optionData) {
                    $optionLink = $this->convertServerQueryToClientUrl(
                        $optionData['searchParams']
                    );

                    $sortOptions[] = new Item($optionData['description'], $optionLink, $optionData['selected']);
                }
            }

            return new Sorting($sortOptions);
        }
        return null;
    }

    /**
     * @return \FACTFinder\Data\SortingItems
     */
    private function createSortingItems()
    {
        $sortOptions = [];

        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData)) {
            $sortingData = $jsonData['searchResult']['sortsList'];
            if (!empty($sortingData)) {
                foreach ($sortingData as $optionData) {
                    $optionLink = $this->convertServerQueryToClientUrl($optionData['searchParams']);
                    $order = SortingDirection::Descending();
                    if (isset($optionData['order']) && $optionData['order'] === 'asc') {
                        $order = SortingDirection::Ascending();
                    }

                    $sortOptions[] = new SortingItem(
                        $optionData['name'],
                        $order,
                        $optionData['description'],
                        $optionLink,
                        $optionData['selected']
                    );
                }
            }

            return new SortingItems($sortOptions);
        }

        return null;
    }

    /**
     * @return \FACTFinder\Data\BreadCrumbTrail
     */
    private function createBreadCrumbTrail()
    {
        $breadCrumbs = [];

        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData)) {
            $breadCrumbTrailData = $jsonData['searchResult']['breadCrumbTrailItems'];
            if (!empty($breadCrumbTrailData)) {
                $i = 1;
                foreach ($breadCrumbTrailData as $breadCrumbData) {
                    $breadCrumbLink = $this->convertServerQueryToClientUrl($breadCrumbData['searchParams']);

                    switch ($breadCrumbData['type']) {
                        case 'filter':
                            $type = BreadCrumbType::Filter();
                            break;
                        case 'advisor':
                            $type = BreadCrumbType::Advisor();
                            break;
                        default:
                            $type = BreadCrumbType::Search();
                            break;
                    }

                    $breadCrumbs[] = new BreadCrumb(
                        $breadCrumbData['text'],
                        $breadCrumbLink,
                        $i == count($breadCrumbTrailData),
                        $type,
                        $breadCrumbData['associatedFieldName']
                    );

                    ++$i;
                }
            }
        }

        return new BreadCrumbTrail($breadCrumbs);
    }

    /**
     * @return \FACTFinder\Data\CampaignIterator
     */
    private function createCampaigns()
    {
        $campaigns = [];
        $jsonData = $this->getResponseContent();

        if ($this->isValidResponse($jsonData) && isset($jsonData['searchResult']['campaigns'])) {
            foreach ($jsonData['searchResult']['campaigns'] as $campaignData) {
                $campaign = $this->createEmptyCampaignObject($campaignData);

                $this->fillCampaignObject($campaign, $campaignData);

                $campaigns[] = $campaign;
            }
        }

        return new CampaignIterator($campaigns);
    }

    /**
     * @param mixed[] $campaignData An associative array corresponding to the
     *                              JSON for a single campaign.
     *
     * @return \FACTFinder\Data\Campaign
     */
    private function createEmptyCampaignObject(array $campaignData)
    {
        return new Campaign($campaignData['name'], $campaignData['category'], $campaignData['target']['destination']);
    }

    /**
     * @param \FACTFinder\Data\Campaign $campaign     The campaign object to be
     *                                                filled.
     * @param mixed[]                   $campaignData An associative array corresponding to the
     *                                                JSON for that campaign.
     */
    private function fillCampaignObject(\FACTFinder\Data\Campaign $campaign, array $campaignData)
    {
        switch ($campaignData['flavour']) {
            case 'FEEDBACK':
                $this->fillCampaignWithFeedback($campaign, $campaignData);
                $this->fillCampaignWithPushedProducts($campaign, $campaignData);
                break;
            case 'ADVISOR':
                $this->fillCampaignWithAdvisorData($campaign, $campaignData);
                break;
        }
    }

    /**
     * @param \FACTFinder\Data\Campaign $campaign     The campaign object to be
     *                                                filled.
     * @param mixed[]                   $campaignData An associative array corresponding to the
     *                                                JSON for that campaign.
     */
    private function fillCampaignWithPushedProducts(\FACTFinder\Data\Campaign $campaign, array $campaignData)
    {
        if (!empty($campaignData['pushedProductsRecords'])) {
            $pushedProducts = [];

            foreach ($campaignData['pushedProductsRecords'] as $recordData) {
                $pushedProducts[] = new Record((string)$recordData['id'], $recordData['record']);
            }

            $campaign->addPushedProducts($pushedProducts);
        }
    }

    /**
     * @param \FACTFinder\Data\Campaign $campaign     The campaign object to be
     *                                                filled.
     * @param mixed[]                   $campaignData An associative array corresponding to the
     *                                                JSON for that campaign.
     */
    private function fillCampaignWithAdvisorData(\FACTFinder\Data\Campaign $campaign, array $campaignData)
    {
        $activeQuestions = [];

        foreach ($campaignData['activeQuestions'] as $questionData) {
            $activeQuestions[] = $this->createAdvisorQuestion($questionData);
        }

        $campaign->addActiveQuestions($activeQuestions);

        // Fetch advisor tree if it exists
        $advisorTree = [];

        foreach ($campaignData['activeQuestions'] as $questionData) {
            $activeQuestions[] = $this->createAdvisorQuestion($questionData, true);
        }

        $campaign->addToAdvisorTree($advisorTree);
    }

    /**
     * @param mixed[] $questionData An associative array corresponding to the
     *                              JSON for a single advisor question.
     * @param bool    $recursive    If this is set the entire advisor tree below this
     *                              question will be created. Otherwise, follow-up questions of
     *                              answers are omitted.
     */
    private function createAdvisorQuestion($questionData, $recursive = false)
    {
        $answers = [];

        foreach ($questionData['answers'] as $answerData) {
            $answers[] = $this->createAdvisorAnswer($answerData, $recursive);
        }

        return new AdvisorQuestion($questionData['text'], $answers);
    }

    /**
     * @param mixed[] $answerData An associative array corresponding to the
     *                            JSON for a single advisor answer.
     * @param bool    $recursive  If this is set the entire advisor tree below the
     *                            subquestion of this ansewr will be created as well.
     */
    private function createAdvisorAnswer($answerData, $recursive = false)
    {
        $params = $this->convertServerQueryToClientUrl($answerData['params']);

        $followUpQuestions = [];
        if ($recursive) {
            foreach ($answerData['questions'] as $questionData) {
                $followUpQuestions[] = $this->createAdvisorQuestion($questionData, true);
            }
        }

        return new AdvisorAnswer($answerData['text'], $params, $followUpQuestions);
    }
}
