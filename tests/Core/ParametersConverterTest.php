<?php

namespace FACTFinder\Test\Core;

use FACTFinder\Core\ParametersConverter;
use FACTFinder\Util\Parameters;

class ParametersConverterTest extends \FACTFinder\Test\BaseTestCase
{
    /**
     * @var FACTFinder\Core\ParametersConverter the converter under test
     */
    private $parametersConverter;

    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration(static::class);

        $this->parametersConverter = new ParametersConverter($configuration);
    }

    public function testClientToServerConversion()
    {
        $clientParameters = new Parameters(
            [
                'keywords'        => 'test',
                'username'        => 'admin',
                'productsPerPage' => '12',
                'shop'            => 'main',
                'filterSize'      => '10',
            ]
        );

        $expectedServerParameters = [
            'query'           => 'test',
            'productsPerPage' => '12',
            'channel'         => 'de',
            'filterSize'      => '10',
        ];

        $actualServerParameters = $this->parametersConverter
            ->convertClientToServerParameters(
                $clientParameters
            );

        $this->assertEquals(
            $expectedServerParameters,
            $actualServerParameters->getArray()
        );
    }

    public function testOverwriteChannel()
    {
        $clientParameters = new Parameters(
            [
                'channel' => 'en',
            ]
        );

        $expectedServerParameters = [
            'channel' => 'en',
        ];

        $actualServerParameters = $this->parametersConverter
            ->convertClientToServerParameters(
                $clientParameters
            );

        $this->assertEquals(
            $expectedServerParameters,
            $actualServerParameters->getArray()
        );
    }

    public function testServerToClientConversion()
    {
        $serverParameters = new Parameters(
            [
                'query'           => 'test',
                'username'        => 'admin',
                'format'          => 'xml',
                'xml'             => 'true',
                'timestamp'       => '123456789',
                'password'        => 'test',
                'channel'         => 'de',
                'productsPerPage' => '12',
                'any'             => 'something',
            ]
        );

        $expectedClientParameters = [
            'keywords'        => 'test',
            'productsPerPage' => '12',
        ];

        $actualClientParameters = $this->parametersConverter
            ->convertServerToClientParameters(
                $serverParameters
            );

        $this->assertEquals(
            $expectedClientParameters,
            $actualClientParameters->getArray()
        );
    }
}
