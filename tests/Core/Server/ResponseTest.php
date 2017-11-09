<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\Response;

class ResponseTest extends \FACTFinder\Test\BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testResponse()
    {
        $response = new Response('response content', 200, CURLE_OK, 'CURLE_OK');

        $this->assertEquals('response content', $response->getContent());
        $this->assertEquals(200, $response->getHttpCode());
        $this->assertEquals(CURLE_OK, $response->getConnectionErrorCode());
        $this->assertEquals('CURLE_OK', $response->getConnectionError());
    }
}
