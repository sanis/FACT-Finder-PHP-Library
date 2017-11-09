<?php

namespace FACTFinder\Data;

class BreadCrumb extends Item
{
    /**
     * @var BreadCrumbType
     */
    private $type;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param string         $label
     * @param string         $url
     * @param bool           $isSelected
     * @param BreadCrumbType $type
     * @param string         $fieldName Only for filter-type items. The field by which
     *                                  was filtered.
     */
    public function __construct($label, $url, $isSelected = false, BreadCrumbType $type = null, $fieldName = '')
    {
        parent::__construct($label, $url, $isSelected);

        $this->type = $type ?: BreadCrumbType::Search();
        $this->fieldName = (string)$fieldName;
    }

    /**
     * @return bool
     */
    public function isSearchBreadCrumb()
    {
        return $this->type == BreadCrumbType::Search();
    }

    /**
     * @return bool
     */
    public function isFilterBreadCrumb()
    {
        return $this->type == BreadCrumbType::Filter();
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }
}
