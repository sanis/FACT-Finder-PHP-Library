<?php

namespace FACTFinder\Data;

/**
 * Represents a particular clickable sorting.
 */
class SortingItem extends Item
{
    /**
     * @var SortingDirection
     */
    private $order;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string           $name
     * @param SortingDirection $order
     * @param string           $label
     * @param string           $url
     * @param bool             $isSelected
     */
    public function __construct(
        $name,
        SortingDirection $order,
        $label,
        $url,
        $isSelected = false
    ) {
        parent::__construct($label, $url, $isSelected);
        $this->name = (string)$name;
        $this->order = $order;
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
    public function isAscending()
    {
        return $this->order == SortingDirection::Ascending();
    }

    /**
     * @return bool
     */
    public function isDescending()
    {
        return $this->order == SortingDirection::Descending();
    }
}
