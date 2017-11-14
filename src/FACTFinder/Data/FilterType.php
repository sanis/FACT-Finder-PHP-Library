<?php
// @codingStandardsIgnoreFile
namespace FACTFinder\Data;

/**
 * Enum for filter types of groups within the After Search Navigation (ASN).
 *
 * @see FilterStyle for documentation of the enum workaround.
 */
class FilterType
{
    // These will store distinct instances of the class.
    static private $text;
    static private $number;

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
            self::$text = new FilterType();
            self::$number = new FilterType();

            self::$initialized = true;
        }
    }

    public static function Text()
    {
        return self::$text;
    }

    public static function Number()
    {
        return self::$number;
    }
}

FilterType::initialize();
