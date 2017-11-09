<?php

namespace FACTFinder\Test\Data;

class FilterSelectionType extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var string
     */
    protected $selectionTypeClass;

    public function setUp()
    {
        parent::setUp();

        $this->selectionTypeClass = get_class(new \FACTFinder\Data\FilterSelectionType());
    }

    public function testTypeSafety()
    {
        $selectionTypeClass = $this->selectionTypeClass;
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::SingleHideUnselected());
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::SingleShowUnselected());
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::MultiSelectOr());
        $this->assertInstanceOf($selectionTypeClass, $selectionTypeClass::MultiSelectAnd());
    }

    public function testEquality()
    {
        $selectionTypeClass = $this->selectionTypeClass;
        $this->assertTrue($selectionTypeClass::SingleHideUnselected() == $selectionTypeClass::SingleHideUnselected());
        $this->assertTrue($selectionTypeClass::SingleShowUnselected() == $selectionTypeClass::SingleShowUnselected());
        $this->assertTrue($selectionTypeClass::MultiSelectOr() == $selectionTypeClass::MultiSelectOr());
        $this->assertTrue($selectionTypeClass::MultiSelectAnd() == $selectionTypeClass::MultiSelectAnd());
    }
}
