<?php

namespace FACTFinder\Test\Data;

use FACTFinder\Data\FilterType;

class FilterTypeTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var string
     */
    protected $typeClass;

    public function setUp()
    {
        parent::setUp();

        $this->typeClass = get_class(new FilterType());
    }

    public function testTypeSafety()
    {
        $typeClass = $this->typeClass;
        $this->assertInstanceOf($typeClass, $typeClass::Text());
        $this->assertInstanceOf($typeClass, $typeClass::Number());
    }

    public function testEquality()
    {
        $typeClass = $this->typeClass;
        $this->assertTrue($typeClass::Text() == $typeClass::Text());
        $this->assertTrue($typeClass::Number() == $typeClass::Number());
    }
}
