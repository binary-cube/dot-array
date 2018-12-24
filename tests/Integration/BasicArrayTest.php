<?php

namespace BinaryCube\DotArray\Tests\Integration;

use BinaryCube\DotArray\DotArray;
use BinaryCube\DotArray\Tests\TestCase;

/**
 * BasicArrayTest
 *
 * @package BinaryCube\DotArray\Tests
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
class BasicArrayTest extends TestCase
{


    /**
     * Testing the Get Method.
     *
     * @return void
     */
    public function testGet()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        self::assertIsArray($dot->get()->toArray());
        self::assertIsArray($dot->get('empty_array')->toArray());

        self::assertIsArray($dot->get('indexed_array')->toArray());
        self::assertIsArray($dot->get('indexed_array.0')->toArray());

        self::assertIsArray($dot->get('assoc_array')->toArray());
        self::assertIsArray($dot->get('assoc_array.two')->toArray());

        self::assertIsArray($dot->get('mixed_array')->toArray());
        self::assertIsArray($dot->get('mixed_array.{👋.🤘.some-key}')->toArray());
        self::assertIsArray($dot->get('mixed_array.{👋.🤘.some-key}.config.elastic-search.{v6.0}')->toArray());
        self::assertIsArray($dot->get('mixed_array.hello-world')->toArray());

        self::assertIsArray($dot['mixed_array']);
        self::assertIsArray($dot['mixed_array.{👋.🤘.some-key}']);

        self::assertIsString($dot->get('mixed_array.hello-world.Romanian'));
        self::assertIsString($dot->get('mixed_array.hello-world.Nǐ hǎo'));
        self::assertIsString($dot->get('mixed_array.hello-world.{Nǐ hǎo}'));

        self::assertIsString($dot['mixed_array.hello-world.{Nǐ hǎo}']);
    }


    /**
     * Testing the Set Method.
     *
     * @return void
     */
    public function testSet()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $dot['a'] = [
            'b' => [
                'c' => 1,
            ],
        ];

        $dot['a']['b']['d'] = 2;

        $dot->set('mixed_array.{new-key}', []);
        $dot->set('mixed_array.{👋.🤘.some-key}', []);

        self::assertIsArray($dot->get('mixed_array.{new-key}')->toArray());
        self::assertEmpty($dot->get('mixed_array.{👋.🤘.some-key}')->toArray());

        self::assertIsInt($dot->get('a.b.c'));
        self::assertIsInt($dot['a']['b']['c']);
        self::assertIsInt($dot['a.b.c']);

        self::assertIsInt($dot->get('a.b.d'));
        self::assertIsInt($dot['a']['b']['d']);
        self::assertIsInt($dot['a.b.d']);
    }


    /**
     * Testing the Has Method.
     *
     * @return void
     */
    public function testHas()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $dot['a'] = [
            'b' => [
                'c' => 1,
            ],
        ];

        self::assertTrue($dot->has(''));
        self::assertTrue($dot->has(null));
        self::assertTrue($dot->has('mixed_array.{👋.🤘.some-key}'));
        self::assertTrue($dot->has('a'));
        self::assertTrue($dot->has('a.b'));
        self::assertTrue($dot->has('a.b.c'));
        self::assertTrue($dot->has('indexed_array.0'));
        self::assertNotTrue($dot->has('indexed_array.10'));
        self::assertNotTrue($dot->has('mixed_array.{imagine dragons}'));
        self::assertNotTrue($dot->has('a.b.c.d'));
    }


    /**
     * Testing the isEmpty Method.
     *
     * @return void
     */
    public function testIsEmpty()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        self::assertIsBool($dot->isEmpty('mixed_array'));
        self::assertIsBool($dot->isEmpty('a'));

        self::assertTrue($dot->isEmpty('empty_array.0'));
        self::assertTrue($dot->isEmpty('a'));
        self::assertNotTrue($dot->isEmpty('mixed_array'));

        self::assertNotTrue(empty($dot['mixed_array']));
        self::assertTrue(empty($dot['a']));
    }


    /**
     * Testing the Delete Method.
     *
     * @return void
     */
    public function testDelete()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $dot->delete('mixed_array.{👋.🤘.some-key}');

        unset($dot['mixed_array.hello-world']);
        unset($dot['assoc_array.two']);

        self::assertArrayNotHasKey('👋.🤘.some-key', $dot->get('mixed_array')->toArray());
        self::assertArrayNotHasKey('hello-world', $dot->get('mixed_array')->toArray());
        self::assertArrayNotHasKey('two', $dot->get('assoc_array')->toArray());

        self::assertArrayNotHasKey('👋.🤘.some-key', $dot['mixed_array']);
        self::assertArrayNotHasKey('hello-world', $dot['mixed_array']);
        self::assertArrayNotHasKey('two', $dot['assoc_array']);

        self::assertNotTrue(array_key_exists('👋.🤘.some-key', $dot['mixed_array']));
        self::assertNotTrue(array_key_exists('hello-world', $dot['mixed_array']));
        self::assertNotTrue(array_key_exists('two', $dot['assoc_array']));

        self::assertTrue(array_key_exists('one', $dot->get('assoc_array')->toArray()));
        self::assertTrue(array_key_exists('one', $dot['assoc_array']));
    }


    /**
     * Testing the Clear Method.
     *
     * @return void
     */
    public function testClear()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $dot->clear('assoc_array.two');
        $dot->clear(['assoc_array.one', 'assoc_array.three']);

        $users = $dot->get('mixed_array.users')->clear();

        self::assertIsArray($dot->get('assoc_array.one')->toArray());
        self::assertIsArray($dot->get('assoc_array.two')->toArray());
        self::assertIsArray($dot->get('assoc_array.three')->toArray());
        self::assertIsArray($users->toArray());

        self::assertEmpty($dot->get('assoc_array.one')->toArray());
        self::assertEmpty($dot->get('assoc_array.two')->toArray());
        self::assertEmpty($dot->get('assoc_array.three')->toArray());
        self::assertEmpty($users->toArray());
    }


    /**
     * Testing the Merge Method.
     *
     * @return void
     */
    public function testMerge()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $extraData = [
            'assoc_array' => [
                'one' => [
                    'element_1' => 'new value for assoc_array.one.element_1',
                ],
            ],

            'mixed_array' => [
                '👋.🤘.some-key' => [
                    'config' => [
                        'elastic-search' => [
                            'v6.0' => [
                                '.other.config' => [
                                    'port' => 9300,
                                ],
                            ],
                        ],

                        'memcached' => [
                            'servers' =>
                                [
                                    [
                                        'host' => '127.0.0.1',
                                        'port' => '12347',
                                    ],
                                ],
                        ]
                    ],
                ],
            ],

            'new-entry' => [
                'a' => [1, 2, 3],
                'b' => new DotArray(
                    [
                        'c' => [
                            'e1' => 1,
                            'e2' => new DotArray(
                                [
                                    'another element'
                                ]
                            ),
                        ],
                    ]
                ),
            ]
        ];

        $dot->merge($extraData);
        self::assertIsArray($dot->get('{new-entry}.a')->toArray());
        self::assertEquals('new value for assoc_array.one.element_1', $dot->get('assoc_array.one.element_1'));
        self::assertEquals(['.other.config' => ['port' => 9300]], $dot->get('mixed_array.{👋.🤘.some-key}.config.{elastic-search}.{v6.0}')->toArray());
        self::assertCount(3, $dot->get('mixed_array.{👋.🤘.some-key}.config.memcached.servers'));
    }


    /**
     * Testing the Count Method.
     *
     * @return void
     */
    public function testCount()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        self::assertCount(2, $dot->get('mixed_array.{👋.🤘.some-key}.config.memcached.servers'));
        self::assertCount(0, $dot->get('empty_array.0'));
        self::assertCount(4, $dot->get('indexed_array'));
        self::assertCount(7, $dot['indexed_array'][0]);

        self::assertEquals(7, count($dot['indexed_array'][0]));
        self::assertEquals(1, count($dot['assoc_array']['three']));
    }


    /**
     * Testing the Find Method.
     *
     * @return void
     */
    public function testFind()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $admin = $dot->get('mixed_array.users')->find(
            function ($value, $key) {
                return (
                    array_key_exists('is_admin', $value)
                    && $value['is_admin'] === true
                );
            }
        );

        self::assertInstanceOf(DotArray::class, $admin);
        self::assertSame(
            [
                'id' => 5,
                'name' => 'User 5',
                'is_admin' => true,
            ],
            $admin->toArray()
        );
    }


    /**
     * Testing the Filter Method.
     *
     * @return void
     */
    public function testFilter()
    {
        $under = DotArray::create([1, 2, 3, 4])->filter(
            function ($value) {
                return ($value % 2) !== 0;
            }
        );

        self::assertSame([0 => 1, 1 => 3], $under->toArray());

        $under = DotArray::create([1, 2, 3, 4])->filter();
        self::assertSame([1, 2, 3, 4], $under->toArray());
    }


    /**
     * Testing the Where Method.
     *
     * @return void
     */
    public function testWhere()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        $users = $dot->get('mixed_array.users')->where(['in', 'id', [3, 4]]);

        self::assertSame(
            [
                [
                    'id' => 3,
                    'name' => 'User 3',
                ],

                [
                    'id' => 4,
                    'name' => 'User 4',
                ],
            ],
            $users->toArray()
        );

        $users = (
            $dot
                ->get('mixed_array')
                ->get('users')
                ->where(
                    function ($value, $key) {
                                return ($value['id'] === 3 || $value['id'] === 4);
                    }
                )
        );

        self::assertSame(
            [
                [
                    'id' => 3,
                    'name' => 'User 3',
                ],

                [
                    'id' => 4,
                    'name' => 'User 4',
                ],
            ],
            $users->toArray()
        );
    }


    /**
     * Testing the toJson Method.
     *
     * @return void
     */
    public function testToJson()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => [
                        'v1.0' => 1.3,
                    ],
                ],
            ],
        ];

        $dot = DotArray::create($data);

        $jsonFromString = '{"a":{"b":{"c":{"v1.0":1.3}}}}';
        $jsonFromObject = $dot->toJson();

        $decode = json_decode($jsonFromObject, true);

        self::assertSame($jsonFromString, $jsonFromObject);
        self::assertSame($data, $decode);
    }


    /**
     * Testing the toArray Method.
     *
     * @return void
     */
    public function testToArray()
    {
        $data = ArrayDataProvider::get();
        $dot  = DotArray::create($data);

        self::assertIsArray($dot->toArray());
    }


}
