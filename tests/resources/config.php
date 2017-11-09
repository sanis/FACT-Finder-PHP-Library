<?php

return [
    'test' => [
        'debug'      => true,
        'custom'     => 'php-value',
        'connection' => [
            'protocol'       => 'http', // possible values: http, https
            'address'        => 'demoshop.fact-finder.de',
            'port'           => 80,
            'context'        => 'FACT-Finder',
            'channel'        => 'de',
            'language'       => 'de',
            'authentication' => [
                'type'     => 'advanced', // possible values: http, simple, advanced
                'username' => 'user',
                'password' => 'userpw',
                'prefix'   => 'FACT-FINDER',
                'postfix'  => 'FACT-FINDER',
            ],
            // all timeouts given in seconds
            'timeouts'       => [
                'defaultConnectTimeout'  => 2,
                'defaultTimeout'         => 4,
                'suggestConnectTimeout'  => 1,
                'suggestTimeout'         => 2,
                'trackingConnectTimeout' => 1,
                'trackingTimeout'        => 2,
                'importConnectTimeout'   => 10,
                'importTimeout'          => 360,
            ],
        ],
        'parameters' => [
            // parameter settings for the server
            'server' => [
                'ignore'    => [
                    'password',
                    'username',
                    'timestamp',
                ],
                'whitelist' => [
                    // no whitelist elements means allow everything
                    // allow search parameters
                    'query',
                    'followSearch',
                    'advisorStatus',
                    '/^filter.*/',
                    '/^sort.*/',
                    'productsPerPage',
                    'navigation',
                    'catalog',
                    'page',
                    'useKeywords',
                    'useFoundWords',
                    'searchField',
                    'omitContextName',
                    'productNumber',
                    'useSemanticEnhancer',
                    'usePersonalization',
                    // allow general settings parameters
                    'channel',
                    'format',
                    'idsOnly',
                    'useAsn',
                    'useCampaigns',
                    'verbose',
                    'log',
                    'do',
                    'callback',
                    // allow suggestions parameters
                    'ignoreForCache',
                    'userInput',
                    'queryFromSuggest',
                    // allow tracking parameters
                    'id',
                    'pos',
                    'sid',
                    'origPos',
                    'page',
                    'simi',
                    'title',
                    'event',
                    'pageSize',
                    'origPageSize',
                    'userId',
                    'cookieId',
                    'masterId',
                    'count',
                    'price',
                    // allow similar/recommandation parameters
                    'maxRecordCount',
                    'maxResults',
                    'mainId',
                    // allow special test cases
                    'a',
                    'c',
                    'ä',
                    'ü',
                    '+ ~',
                ],
                'mapping'   => [
                    ['from' => 'keywords', 'to' => 'query'],
                ],
            ],
            // parameter settings for the client
            'client' => [
                'ignore'    => [
                    'xml',
                    'format',
                    'channel',
                    'password',
                    'username',
                    'timestamp',
                ],
                'whitelist' => [
                    // no whitelist elements means allow everything
                    // allow search parameters
                    'keywords',
                    'followSearch',
                    'advisorStatus',
                    '/^filter.*/',
                    'seoPath',
                    'productsPerPage',
                    'navigation',
                    'catalog',
                    'page',
                    'useKeywords',
                    'useFoundWords',
                    'searchField',
                    'omitContextName',
                    'productNumber',
                    'useSemanticEnhancer',
                    'usePersonalization',
                    // allow general settings parameters
                    'idsOnly',
                    'useAsn',
                    'useCampaigns',
                    'verbose',
                    'log',
                    // allow suggestions parameters
                    'ignoreForCache',
                    'userInput',
                    'queryFromSuggest',
                    // allow special test cases
                    'foo',
                ],
                'mapping'   => [
                    ['from' => 'query', 'to' => 'keywords'],
                ],
            ],
        ],
        'encoding'   => [
            'pageContent' => 'UTF-8',
            'clientUrl'   => 'ISO-8859-1',
        ],
    ],
];
