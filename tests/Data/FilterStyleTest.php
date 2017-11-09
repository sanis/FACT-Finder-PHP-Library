<?php

namespace FACTFinder\Test\Data;

use FACTFinder\Loader as FF;

class FilterStyleTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var string
     */
    protected $styleClass;

    public function setUp()
    {
        parent::setUp();

        $this->styleClass = FF::getClassName('Data\FilterStyle');
    }

    public function testTypeSafety()
    {
        $styleClass = $this->styleClass;
        $this->assertInstanceOf($styleClass, $styleClass::Regular());
        $this->assertInstanceOf($styleClass, $styleClass::Slider());
        $this->assertInstanceOf($styleClass, $styleClass::Tree());
        $this->assertInstanceOf($styleClass, $styleClass::MultiSelect());
    }

    public function testEquality()
    {
        $styleClass = $this->styleClass;
        $this->assertTrue($styleClass::Regular() == $styleClass::Regular());
        $this->assertTrue($styleClass::Slider() == $styleClass::Slider());
        $this->assertFalse($styleClass::Regular() == $styleClass::Slider());
    }
}
