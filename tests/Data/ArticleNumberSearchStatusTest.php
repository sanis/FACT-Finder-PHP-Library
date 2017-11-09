<?php

namespace FACTFinder\Test\Data;

use FACTFinder\Data\ArticleNumberSearchStatus;

class ArticleNumberSearchStatusTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var string
     */
    protected $statusClass;

    public function setUp()
    {
        parent::setUp();

        $this->statusClass = get_class(new ArticleNumberSearchStatus());
    }

    public function testTypeSafety()
    {
        $statusClass = $this->statusClass;
        $this->assertInstanceOf($statusClass, $statusClass::IsArticleNumberResultFound());
        $this->assertInstanceOf($statusClass, $statusClass::IsNoArticleNumberResultFound());
        $this->assertInstanceOf($statusClass, $statusClass::IsNoArticleNumberSearch());
    }

    public function testEquality()
    {
        $statusClass = $this->statusClass;
        $this->assertTrue($statusClass::IsArticleNumberResultFound() == $statusClass::IsArticleNumberResultFound());
        $this->assertTrue($statusClass::IsNoArticleNumberResultFound() == $statusClass::IsNoArticleNumberResultFound());
        $this->assertTrue($statusClass::IsNoArticleNumberSearch() == $statusClass::IsNoArticleNumberSearch());
        $this->assertFalse($statusClass::IsArticleNumberResultFound() == $statusClass::IsNoArticleNumberResultFound());
        $this->assertFalse($statusClass::IsNoArticleNumberResultFound() == $statusClass::IsNoArticleNumberSearch());
        $this->assertFalse($statusClass::IsArticleNumberResultFound() == $statusClass::IsNoArticleNumberSearch());
    }
}
