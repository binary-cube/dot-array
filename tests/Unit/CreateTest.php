<?php

namespace BinaryCube\DotArray\Tests\Unit;

use BinaryCube\DotArray\DotArray;

/**
 * CreateTest
 *
 * @coversDefaultClass \BinaryCube\DotArray\DotArray
 *
 * @package BinaryCube\DotArray\Tests
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
class CreateTest extends TestCase
{

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $jsonString;

    /**
     * Setup the test env.
     *
     * @return void
     */
    public function setUp()
    {
        $this->data       = ArrayDataProvider::get();
        $this->jsonString = '{"a":{"b":{"c":{"v1.0":1.3}}}}';

        parent::setUp();
    }

    /**
     * Testing Object.
     *
     * @covers \BinaryCube\DotArray\DotArray::__construct
     * @covers \BinaryCube\DotArray\DotArray::create
     * @covers \BinaryCube\DotArray\DotArray::createFromJson
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testCreate()
    {
        self::assertInstanceOf(DotArray::class, new DotArray());
        self::assertInstanceOf(DotArray::class, new DotArray([]));
        self::assertInstanceOf(DotArray::class, new DotArray(''));
        self::assertInstanceOf(DotArray::class, new DotArray(DotArray::create($this->data)));
        self::assertInstanceOf(DotArray::class, new DotArray('key 0 in array'));
        self::assertInstanceOf(DotArray::class, new DotArray($this->data));
        self::assertInstanceOf(
            DotArray::class,
            new DotArray(
                [
                    'items' => new DotArray($this->data),
                ]
            )
        );

        self::assertInstanceOf(DotArray::class, DotArray::create([]));
        self::assertInstanceOf(DotArray::class, DotArray::create($this->data));
        self::assertInstanceOf(
            DotArray::class,
            DotArray::create(
                [
                    'items' => new DotArray($this->data),
                ]
            )
        );

        self::assertIsArray(DotArray::create(null)->toArray());
        self::assertIsArray(DotArray::create(1)->toArray());
        self::assertIsArray(DotArray::create([])->toArray());
        self::assertIsArray(DotArray::create(DotArray::create([]))->toArray());

        $dot = new DotArray($this->data);

        self::assertIsArray($dot->toArray());

        self::assertArrayHasKey('empty_array', $dot->toArray());
        self::assertArrayHasKey('indexed_array', $dot->toArray());
        self::assertArrayHasKey('assoc_array', $dot->toArray());
        self::assertArrayHasKey('mixed_array', $dot->toArray());

        // Testing the createFromJson.
        $dot = DotArray::createFromJson($this->jsonString);
        self::assertInstanceOf(DotArray::class, $dot);
        self::assertArrayHasKey('a', $dot->toArray());
    }

}
