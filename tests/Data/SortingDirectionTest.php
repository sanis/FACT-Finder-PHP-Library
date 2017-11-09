<?php

namespace FACTFinder\Test\Data;

use FACTFinder\Data\SortingDirection;

class SortingDirectionTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var string
     */
    protected $directionClass;

    public function setUp()
    {
        parent::setUp();

        $this->directionClass = get_class(new SortingDirection());
    }

    public function testTypeSafety()
    {
        $directionClass = $this->directionClass;
        $this->assertInstanceOf($directionClass, $directionClass::Ascending());
        $this->assertInstanceOf($directionClass, $directionClass::Descending());
    }

    public function testEquality()
    {
        $directionClass = $this->directionClass;
        $this->assertTrue($directionClass::Ascending() == $directionClass::Ascending());
        $this->assertTrue($directionClass::Descending() == $directionClass::Descending());
        $this->assertFalse($directionClass::Ascending() == $directionClass::Descending());
    }
}
