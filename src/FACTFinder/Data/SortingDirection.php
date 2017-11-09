<?php

namespace FACTFinder\Data;

/**
 * Enum for sorting directions.
 *
 * @see FilterStyle for documentation of the enum workaround.
 */
class SortingDirection
{
    // These will store distinct instances of the class.
    static private $asc;
    static private $desc;

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
            self::$asc = new SortingDirection();
            self::$desc = new SortingDirection();

            self::$initialized = true;
        }
    }

    public static function Ascending()
    {
        return self::$asc;
    }

    public static function Descending()
    {
        return self::$desc;
    }
}

SortingDirection::initialize();
