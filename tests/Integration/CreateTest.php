<?php

namespace BinaryCube\DotArray\Tests\Integration;

use BinaryCube\DotArray\DotArray;
use BinaryCube\DotArray\Tests\TestCase;

/**
 * CreateTest
 *
 * @package BinaryCube\DotArray\Tests
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
class CreateTest extends TestCase
{


    /**
     * Testing Object.
     *
     * @return void
     */
    public function testCreate()
    {
        $data = ArrayDataProvider::get();

        self::assertInstanceOf(DotArray::class, new DotArray());
        self::assertInstanceOf(DotArray::class, new DotArray([]));
        self::assertInstanceOf(DotArray::class, new DotArray($data));
        self::assertInstanceOf(
            DotArray::class,
            new DotArray(
                [
                    'items' => new DotArray($data),
                ]
            )
        );

        self::assertInstanceOf(DotArray::class, DotArray::create([]));
        self::assertInstanceOf(DotArray::class, DotArray::create($data));
        self::assertInstanceOf(
            DotArray::class,
            DotArray::create(
                [
                    'items' => new DotArray($data),
                ]
            )
        );

        $dot = new DotArray($data);

        self::assertIsArray($dot->toArray());
        self::assertArrayHasKey('empty_array', $dot->toArray());
        self::assertArrayHasKey('indexed_array', $dot->toArray());
        self::assertArrayHasKey('assoc_array', $dot->toArray());
        self::assertArrayHasKey('mixed_array', $dot->toArray());
    }


}
