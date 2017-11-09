<?php

namespace FACTFinder\Data;

/**
 * Enum for status of the search result.
 *
 * @see FilterStyle for documentation of the enum workaround.
 */
class SearchStatus
{
    static private $noQuery;
    static private $noResult;
    static private $emptyResult;
    static private $recordsFound;

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
            self::$noQuery = new SearchStatus();
            self::$noResult = new SearchStatus();
            self::$emptyResult = new SearchStatus();
            self::$recordsFound = new SearchStatus();

            self::$initialized = true;
        }
    }

    public static function NoQuery()
    {
        return self::$noQuery;
    }

    public static function NoResult()
    {
        return self::$noResult;
    }

    public static function EmptyResult()
    {
        return self::$emptyResult;
    }

    public static function RecordsFound()
    {
        return self::$recordsFound;
    }
}

SearchStatus::initialize();
