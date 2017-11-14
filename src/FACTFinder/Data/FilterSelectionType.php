<?php
// @codingStandardsIgnoreFile
namespace FACTFinder\Data;

/**
 * Enum for selection types of filter groups within the After Search Navigation (ASN).
 *
 * @see FilterStyle for documentation of the enum workaround.
 */
class FilterSelectionType
{
    // These will store distinct instances of the class.
    static private $singleHideUnselected;
    static private $singleShowUnselected;
    static private $multiSelectOr;
    static private $multiSelectAnd;

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
            self::$singleHideUnselected = new FilterSelectionType();
            self::$singleShowUnselected = new FilterSelectionType();
            self::$multiSelectOr = new FilterSelectionType();
            self::$multiSelectAnd = new FilterSelectionType();

            self::$initialized = true;
        }
    }

    public static function SingleHideUnselected()
    {
        return self::$singleHideUnselected;
    }

    public static function SingleShowUnselected()
    {
        return self::$singleShowUnselected;
    }

    public static function MultiSelectOr()
    {
        return self::$multiSelectOr;
    }

    public static function MultiSelectAnd()
    {
        return self::$multiSelectAnd;
    }
}

FilterSelectionType::initialize();
