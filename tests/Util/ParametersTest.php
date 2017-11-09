<?php

namespace FACTFinder\Test\Util;

use FACTFinder\Util\Parameters;


class ParameterTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Util\Parameters
     */
    protected $parameters;

    public function setUp()
    {
        parent::setUp();

        $this->parameters = new Parameters();
    }

    public function testSetSingleParameter()
    {
        $this->parameters['query'] = 'bmx';
        $this->assertParameters(['query' => 'bmx']);

        $this->parameters['format'] = 'json';
        $this->assertParameters(
            [
                'query'  => 'bmx',
                'format' => 'json',
            ]
        );

        $this->parameters['format'] = 'xml';
        $this->assertParameters(
            [
                'query'  => 'bmx',
                'format' => 'xml',
            ]
        );
    }

    public function testSetMultipleParameters()
    {
        $this->parameters['id'] = ['123', '456'];
        $this->assertParameters(
            [
                'id' => ['123', '456'],
            ]
        );

        $this->parameters['id'] = ['456', '789'];
        $this->assertParameters(
            [
                'id' => ['456', '789'],
            ]
        );
    }

    public function testSingleElementArray()
    {
        $this->parameters['id'] = ['123'];
        $this->assertParameters(
            [
                'id' => '123',
            ]
        );
    }

    public function testAddSingleParameter()
    {
        $this->parameters->add('query', 'bmx');
        $this->assertParameters(['query' => 'bmx']);

        $this->parameters->add('id', '123');
        $this->assertParameters(
            [
                'query' => 'bmx',
                'id'    => '123',
            ]
        );

        $this->parameters->add('id', '456');
        $this->assertParameters(
            [
                'query' => 'bmx',
                'id'    => ['123', '456'],
            ]
        );

        $this->parameters->add('id', '789');
        $this->assertParameters(
            [
                'query' => 'bmx',
                'id'    => ['123', '456', '789'],
            ]
        );
    }

    public function testAddMultipleParameters()
    {
        $this->parameters->add('id', ['123', '456']);
        $this->assertParameters(
            [
                'id' => ['123', '456'],
            ]
        );

        $this->parameters->add('id', ['789', 'abc']);
        $this->assertParameters(
            [
                'id' => ['123', '456', '789', 'abc'],
            ]
        );

        $this->parameters['id'] = '123';
        $this->parameters->add('id', ['456', '789']);
        $this->assertParameters(
            [
                'id' => ['123', '456', '789'],
            ]
        );
    }

    public function testAddSingleElementArray()
    {
        $this->parameters->add('id', ['123']);
        $this->assertParameters(
            [
                'id' => '123',
            ]
        );

        $this->parameters->add('id', ['456']);
        $this->assertParameters(
            [
                'id' => ['123', '456'],
            ]
        );

        $this->parameters->add('id', ['789']);
        $this->assertParameters(
            [
                'id' => ['123', '456', '789'],
            ]
        );
    }

    public function testToString()
    {
        $this->parameters->setAll(
            [
                'query' => 'bmx',
                'id'    => ['123', '456'],
                'a b'   => 'c d',
            ]
        );

        $expectedHttpHeaders = [
            'query: bmx',
            'id: 123,456',
            'a b: c d',
        ];

        // These assertions are actually too rigid, because we don't really want
        // to make any assumptions about the order of the parameters.
        $this->assertEquals(
            'query=bmx&id%5B0%5D=123&id%5B1%5D=456&a%20b=c%20d',
            $this->parameters->toPhpQueryString()
        );
        $this->assertEquals(
            'query=bmx&id=123&id=456&a%20b=c%20d',
            $this->parameters->toJavaQueryString()
        );

        // Here, the order is actually important.
        $this->assertEquals(
            $expectedHttpHeaders,
            $this->parameters->toHttpHeaderFields()
        );
    }

    public function testClone()
    {
        $this->parameters['id'] = '123';

        $newParameters = clone $this->parameters;

        $newParameters['query'] = 'bmx';
        $newParameters->add('id', '456');

        $this->assertParameters(['id' => '123']);

        $this->parameters = $newParameters;

        $this->assertParameters(
            [
                'id'    => ['123', '456'],
                'query' => 'bmx',
            ]
        );
    }

    public function testCount()
    {
        $this->assertEquals(0, count($this->parameters));

        $this->parameters['format'] = 'json';
        $this->assertEquals(1, count($this->parameters));

        $this->parameters['id'] = ['123', '456'];
        $this->assertEquals(3, count($this->parameters));

        $this->parameters->add('id', ['789', 'abc']);
        $this->assertEquals(5, count($this->parameters));

        $this->parameters['id'] = ['123', '456', '789'];
        $this->assertEquals(4, count($this->parameters));

        unset($this->parameters['format']);
        $this->assertEquals(3, count($this->parameters));

        $this->parameters->clear();
        $this->assertEquals(0, count($this->parameters));
    }

    public function testParametersFromPhpQueryString()
    {

        $parameters = new Parameters(
            'a%20b=c&d=e%20f'
        );

        $expectedParameters = [
            'a b' => 'c',
            'd'   => 'e f',
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromPhpUrl()
    {

        $parameters = new Parameters(
            'index.php?a=b&c=d'
        );

        $expectedParameters = [
            'a' => 'b',
            'c' => 'd',
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromPhpQueryStringWithMultipleValues()
    {
        $parameters = new Parameters(
            'a=1&a=2&b[]=3&b[]=4&b%5B%5D=5'
        );

        $expectedParameters = [
            'a' => '2',
            'b' => ['3', '4', '5'],
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromPhpQueryStringWithEmptyParameterNames()
    {
        $parameters = new Parameters(
            '=1&&[]=3&%5B%5D=4'
        );

        $expectedParameters = [
            '' => ['', '3', '4'],
        ];

        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromJavaQueryString()
    {

        $parameters = new Parameters(
            'a+b=c&d=e+f',
            true
        );

        $expectedParameters = [
            'a b' => 'c',
            'd'   => 'e f',
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromJavaUrl()
    {

        $parameters = new Parameters(
            'index.php?a=b&c=d',
            true
        );

        $expectedParameters = [
            'a' => 'b',
            'c' => 'd',
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromJavaQueryStringWithMultipleValues()
    {
        $parameters = new Parameters(
            'a=1&a=2&b=3&b=4&b[]=5',
            true
        );

        $expectedParameters = [
            'a'   => ['1', '2'],
            'b'   => ['3', '4'],
            'b[]' => '5',
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testParametersFromJavaQueryStringWithEmptyParameterNames()
    {
        $parameters = new Parameters(
            '=1&=2&&=&=3',
            true
        );

        $expectedParameters = [
            '' => ['1', '2', '', '3'],
        ];
        $this->assertEquals($expectedParameters, $parameters->getArray());
    }

    public function testGetArray()
    {
        $this->parameters['query'] = 'bmx';
        $this->parameters['format'] = 'json';

        $array = &$this->parameters->getArray();

        unset($array['format']);

        // $array should have been a reference, so the parameters object should
        // be affected as well.
        $this->assertParameters(['query' => 'bmx']);
    }

    private function assertParameters($expectedParameters)
    {
        $actualParameters = $this->parameters->getArray();
        $this->assertEquals($expectedParameters, $actualParameters);
    }
}
