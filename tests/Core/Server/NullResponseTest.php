<?php

namespace FACTFinder\Test\Core\Server;

use FACTFinder\Core\Server\NullResponse;

class NullResponseTest extends \FACTFinder\Test\BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testResponse()
    {
        $response = new NullResponse();

        $this->assertEquals('', $response->getContent());
        $this->assertEquals(0, $response->getHttpCode());
        $this->assertEquals(0, $response->getConnectionErrorCode());
        $this->assertEquals('', $response->getConnectionError());
    }
}
