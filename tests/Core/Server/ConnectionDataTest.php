<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\ConnectionData;
use FACTFinder\Core\Server\Response;

class ConnectionDataTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\Server\ConnectionData
     */
    private $connectionData;

    public function setUp()
    {
        parent::setUp();

        $this->connectionData = new ConnectionData();
    }

    public function testInitializedEmpty()
    {
        $response = $this->connectionData->getResponse();

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(0, $response->getHttpCode());
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());

        $parameters = $this->connectionData->getParameters();
        $this->assertEquals(0, count($parameters));

        $httpHeaderFields = $this->connectionData->getHttpHeaderFields();
        $this->assertEquals(0, count($httpHeaderFields));

        $connectionOptions = $this->connectionData->getConnectionOptions();
        $this->assertEquals(0, count($connectionOptions));

        $this->assertEquals('', $this->connectionData->getAction());

        $this->assertNull($this->connectionData->getPreviousUrl());
    }

    public function testSetAction()
    {
        $this->connectionData->setAction('Search.ff');
        $this->assertEquals('Search.ff', $this->connectionData->getAction());
    }

    public function testConnectionOptions()
    {
        $cd = $this->connectionData;

        $this->assertFalse($cd->issetConnectionOption('test'));
        $this->assertFalse($cd->issetConnectionOption(16));
        $this->assertFalse($cd->issetConnectionOption(CURLOPT_TIMEOUT));

        $cd->setConnectionOption('test', 'value');
        $cd->setConnectionOption(16, [1, 2, 3]);

        $this->assertTrue($cd->issetConnectionOption('test'));
        $this->assertTrue($cd->issetConnectionOption(16));
        $this->assertFalse($cd->issetConnectionOption(CURLOPT_TIMEOUT));

        $this->assertEquals('value', $cd->getConnectionOption('test'));
        $this->assertEquals([1, 2, 3], $cd->getConnectionOption(16));

        $cd->setConnectionOptions(
            [
                16              => [4, 5, 6],
                CURLOPT_TIMEOUT => $this,
            ]
        );

        $this->assertTrue($cd->issetConnectionOption('test'));
        $this->assertTrue($cd->issetConnectionOption(16));
        $this->assertTrue($cd->issetConnectionOption(CURLOPT_TIMEOUT));

        $this->assertEquals('value', $cd->getConnectionOption('test'));
        $this->assertEquals([4, 5, 6], $cd->getConnectionOption(16));
        $this->assertSame($this, $cd->getConnectionOption(CURLOPT_TIMEOUT));

        $expectedOptions = [
            'test'          => 'value',
            16              => [4, 5, 6],
            CURLOPT_TIMEOUT => $this,
        ];

        $this->assertEquals($expectedOptions, $cd->getConnectionOptions());
    }

    public function testSetResponse()
    {
        $response = new Response(
            'response content',
            200,
            0,
            CURLE_OK
        );

        $this->connectionData->setResponse($response, 'http://test.com');

        $this->assertSame($response, $this->connectionData->getResponse());
        $this->assertEquals('http://test.com', $this->connectionData->getPreviousUrl());

        $this->connectionData->setNullResponse();

        $response = $this->connectionData->getResponse();
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(0, $response->getHttpCode());
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
        $this->assertNull($this->connectionData->getPreviousUrl());
    }
}
