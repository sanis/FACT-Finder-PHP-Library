<?php

namespace FACTFinder\Data;

/**
 * Enum for article number status of the search result.
 *
 * @see FilterStyle for documentation of the enum workaround.
 */
class ArticleNumberSearchStatus
{
    static private $isArticleNumberResultFound;
    static private $isNoArticleNumberResultFound;
    static private $isNoArticleNumberSearch;

    static private $nextID = 0;
    static private $initialized = false;
    private $id;

    private function __construct()
    {
        $this->id = self::$nextID++;
    }

    public static function initialize()
    {
        if (!self::$initialized) {
            self::$isArticleNumberResultFound = new ArticleNumberSearchStatus();
            self::$isNoArticleNumberResultFound = new ArticleNumberSearchStatus();
            self::$isNoArticleNumberSearch = new ArticleNumberSearchStatus();

            self::$initialized = true;
        }
    }

    public static function IsArticleNumberResultFound()
    {
        return self::$isArticleNumberResultFound;
    }

    public static function IsNoArticleNumberResultFound()
    {
        return self::$isNoArticleNumberResultFound;
    }

    public static function IsNoArticleNumberSearch()
    {
        return self::$isNoArticleNumberSearch;
    }
}

ArticleNumberSearchStatus::initialize();
