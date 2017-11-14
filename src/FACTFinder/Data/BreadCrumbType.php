<?php
// @codingStandardsIgnoreFile
namespace FACTFinder\Data;

/**
 * Enum for type of a bread crumb item.
 *
 * @see FilterStyle for documentation of the enum workaround.
 */
class BreadCrumbType
{
    static private $search;
    static private $filter;
    static private $advisor;

    static private $nextID = 0;
    static private $initialized = false;
    private $id;

    public function __construct()
    {
        $this->id = self::$nextID++;
    }

    public static function initialize()
    {
        if (!self::$initialized) {
            self::$search = new BreadCrumbType();
            self::$filter = new BreadCrumbType();
            self::$advisor = new BreadCrumbType();

            self::$initialized = true;
        }
    }

    public static function Search()
    {
        return self::$search;
    }

    public static function Filter()
    {
        return self::$filter;
    }

    public static function Advisor()
    {
        return self::$advisor;
    }
}

BreadCrumbType::initialize();
