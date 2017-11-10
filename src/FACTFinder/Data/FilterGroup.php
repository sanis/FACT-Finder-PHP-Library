<?php

namespace FACTFinder\Data;

/**
 * A group of filters within the After Search Navigation (ASN).
 */
class FilterGroup extends \ArrayIterator
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var FilterStyle
     */
    private $style;

    /**
     * @var int
     */
    private $detailedLinkCount;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var FilterSelectionType
     */
    private $selectionType;

    /**
     * @var FilterType
     */
    private $type;

    /**
     * @var bool
     */
    private $showPreviewImages;

    /**
     * @param Filter[]                 $filters The Filter objects to add to the group.
     * @param string                   $name
     * @param FilterStyle|null         $style
     * @param int                      $detailedLinkCount
     * @param string                   $unit
     * @param FilterSelectionType|null $selectionType
     * @param FilterType|null          $type
     * @param bool                     $showPreviewImages
     *
     * @throws \Exception
     */
    public function __construct(
        array $filters = [],
        $name = '',
        FilterStyle $style = null,
        $detailedLinkCount = 0,
        $unit = '',
        FilterSelectionType $selectionType = null,
        FilterType $type = null,
        $showPreviewImages = false
    ) {
        parent::__construct($filters);

        $this->name = (string)$name;
        $this->style = $style ?: FilterStyle::Regular();
        $this->detailedLinkCount = (int)$detailedLinkCount;
        $this->unit = (string)$unit;
        $this->selectionType = $selectionType ?: FilterSelectionType::SingleHideUnselected();
        $this->type = $type ?: FilterType::Text();
        $this->showPreviewImages = (bool)$showPreviewImages;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRegularStyle()
    {
        return $this->style == FilterStyle::Regular();
    }

    /**
     * @return bool
     */
    public function isSliderStyle()
    {
        return $this->style == FilterStyle::Slider();
    }

    /**
     * @return bool
     */
    public function isTreeStyle()
    {
        return $this->style == FilterStyle::Tree();
    }

    /**
     * @return bool
     */
    public function isMultiSelectStyle()
    {
        return $this->style == FilterStyle::MultiSelect();
    }

    /**
     * @return int
     */
    public function getDetailedLinkCount()
    {
        return $this->detailedLinkCount;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return bool
     */
    public function hasPreviewImages()
    {
        return $this->showPreviewImages;
    }

    /**
     * @return bool
     */
    public function hasSelectedItems()
    {
        /** @var Filter $filter */
        foreach ($this->getArrayCopy() as $filter) {
            if ($filter->isSelected()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSingleHideUnselectedType()
    {
        return $this->selectionType == FilterSelectionType::SingleHideUnselected();
    }

    /**
     * @return bool
     */
    public function isSingleShowUnselectedType()
    {
        return $this->selectionType == FilterSelectionType::SingleShowUnselected();
    }

    /**
     * @return bool
     */
    public function isMultiSelectOrType()
    {
        return $this->selectionType == FilterSelectionType::MultiSelectOr();
    }

    /**
     * @return bool
     */
    public function isMultiSelectAndType()
    {
        return $this->selectionType == FilterSelectionType::MultiSelectAnd();
    }

    /**
     * @return bool
     */
    public function isTextType()
    {
        return $this->type == FilterType::Text();
    }

    /**
     * @return bool
     */
    public function isNumberType()
    {
        return $this->type == FilterType::Number();
    }

}
