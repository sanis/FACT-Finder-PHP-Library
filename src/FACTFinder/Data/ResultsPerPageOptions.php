<?php

namespace FACTFinder\Data;

class ResultsPerPageOptions extends \ArrayIterator
{
    /**
     * @var Item
     */
    private $defaultOption;

    /**
     * @var Item
     */
    private $selectOption;

    public function __construct(
        array $options,
        Item $defaultOption = null,
        Item $selectedOption = null
    ) {
        parent::__construct($options);

        if (null !== $defaultOption) {
            $this->defaultOption = $defaultOption;
        } elseif (count($options)) {
            $this->defaultOption = $options[0];
        }

        if (null !== $selectedOption) {
            $this->selectedOption = $selectedOption;
        } elseif (null !== $this->defaultOption) {
            $this->selectedOption = $this->defaultOption;
        }
    }

    /**
     * @return Item
     */
    public function getDefaultOption()
    {
        return $this->defaultOption;
    }

    /**
     * @return Item
     */
    public function getSelectedOption()
    {
        return $this->selectedOption;
    }
}
