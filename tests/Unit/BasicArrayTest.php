<?php

namespace BinaryCube\DotArray\Tests\Unit;

use BinaryCube\DotArray\DotArray;

/**
 * BasicArrayTest
 *
 * @coversDefaultClass \BinaryCube\DotArray\DotArray
 *
 * @package BinaryCube\DotArray\Tests
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
class BasicArrayTest extends TestCase
{

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $jsonArray;

    /**
     * @var string
     */
    protected $jsonString;

    /**
     * @var DotArray
     */
    protected $dot;

    /**
     * Setup the test env.
     *
     * @return void
     */
    public function setUp()
    {
        $this->data       = ArrayDataProvider::get();
        $this->jsonArray  = [
            'a' => [
                'b' => [
                    'c' => [
                        'v1.0' => 1.3,
                    ],
                ],
            ],
        ];
        $this->jsonString = '{"a":{"b":{"c":{"v1.0":1.3}}}}';
        $this->dot        = new DotArray($this->data);

        parent::setUp();
    }

    /**
     * Testing the Get Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::get
     * @covers \BinaryCube\DotArray\DotArray::toArray
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testGet()
    {
        $dot = $this->dot;

        self::assertIsArray($dot()->toArray());

        self::assertIsArray($this->dot->get()->toArray());
        self::assertIsArray($this->dot->get('empty_array')->toArray());

        self::assertIsArray($this->dot->get('indexed_array')->toArray());
        self::assertIsArray($this->dot->get('indexed_array.0')->toArray());

        self::assertIsArray($this->dot->get('assoc_array')->toArray());
        self::assertIsArray($this->dot->get('assoc_array.two')->toArray());

        self::assertIsArray($this->dot->get('mixed_array')->toArray());
        self::assertIsArray($this->dot->get('mixed_array.{👋.🤘.some-key}')->toArray());
        self::assertIsArray($this->dot->get('mixed_array.{👋.🤘.some-key}.config.elastic-search.{v6.0}')->toArray());
        self::assertIsArray($this->dot->get('mixed_array.hello-world')->toArray());

        self::assertIsArray($this->dot['mixed_array']);
        self::assertIsArray($this->dot['mixed_array.{👋.🤘.some-key}']);

        self::assertIsString($this->dot->get('mixed_array.hello-world.Romanian'));
        self::assertIsString($this->dot->get('mixed_array.hello-world.Nǐ hǎo'));
        self::assertIsString($this->dot->get('mixed_array.hello-world.{Nǐ hǎo}'));

        self::assertIsString($this->dot['mixed_array.hello-world.{Nǐ hǎo}']);
    }

    /**
     * Testing the Set Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::set
     * @covers \BinaryCube\DotArray\DotArray::toArray
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testSet()
    {
        $this->dot['a'] = [
            'b' => [
                'c' => 3,
            ],
        ];

        $this->dot['a']['b']['b']   = 2;
        $this->dot['a']['b']['obj'] = [
            DotArray::create(['key' => 'was a dot object 1']),
            DotArray::create(['key' => 'was a dot object 2']),
        ];

        $this->dot->set('mixed_array.{new-key}', []);
        $this->dot->set('mixed_array.{👋.🤘.some-key}', []);
        $this->dot->set('a.b.a', 1);
        $this->dot->set('new1.new2', 1);
        $this->dot->set('{dot.obj}', DotArray::create('some_value'));

        self::assertIsArray($this->dot->get('mixed_array.{new-key}')->toArray());
        self::assertEmpty($this->dot->get('mixed_array.{👋.🤘.some-key}')->toArray());

        self::assertIsArray($this->dot->get('{dot.obj}')->toArray());
        self::assertIsArray($this->dot->get('a.b.obj')->toArray());
        self::assertIsArray($this->dot->get('a.b.obj.0')->toArray());

        self::assertIsInt($this->dot->get('a.b.a'));
        self::assertIsInt($this->dot['a']['b']['a']);
        self::assertIsInt($this->dot['a.b.a']);

        self::assertIsInt($this->dot->get('a.b.c'));
        self::assertIsInt($this->dot['a']['b']['c']);
        self::assertIsInt($this->dot['a.b.c']);

        self::assertIsInt($this->dot->get('a.b.b'));
        self::assertIsInt($this->dot['a']['b']['b']);
        self::assertIsInt($this->dot['a.b.b']);

        self::assertIsInt($this->dot->get('new1.new2'));
        self::assertIsInt($this->dot['new1']['new2']);
        self::assertIsInt($this->dot['new1.new2']);

        self::assertSame([], $this->dot->set(null, null)->toArray());
    }

    /**
     * Testing the Has Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::has
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testHas()
    {
        $this->dot['a'] = [
            'b' => [
                'c' => 1,
            ],
        ];

        self::assertTrue($this->dot->has(''));
        self::assertTrue($this->dot->has(null));
        self::assertTrue($this->dot->has('mixed_array.{👋.🤘.some-key}'));
        self::assertTrue($this->dot->has('a'));
        self::assertTrue($this->dot->has('a.b'));
        self::assertTrue($this->dot->has('a.b.c'));
        self::assertTrue($this->dot->has('indexed_array.0'));
        self::assertNotTrue($this->dot->has('indexed_array.10'));
        self::assertNotTrue($this->dot->has('mixed_array.{imagine dragons}'));
        self::assertNotTrue($this->dot->has('a.b.c.d'));
    }

    /**
     * Testing the isEmpty Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::isEmpty
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testIsEmpty()
    {
        self::assertIsBool($this->dot->isEmpty());
        self::assertIsBool($this->dot->isEmpty(null));
        self::assertIsBool($this->dot->isEmpty('mixed_array'));
        self::assertIsBool($this->dot->isEmpty('a'));

        self::assertNotTrue($this->dot->isEmpty('mixed_array'));
        self::assertTrue($this->dot->isEmpty('a'));
        self::assertTrue($this->dot->isEmpty('empty_array.0'));

        self::assertNotTrue(empty($this->dot['mixed_array']));
        self::assertTrue(empty($this->dot['a']));

        $this->dot->set('dotObject', DotArray::create([]));

        self::assertIsBool($this->dot->isEmpty('dotObject'));
        self::assertIsBool($this->dot->get('dotObject')->isEmpty());
    }

    /**
     * Testing the Delete Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::delete
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testDelete()
    {
        $this->dot->delete('mixed_array.{👋.🤘.some-key}');

        unset($this->dot['empty_array']);
        unset($this->dot['mixed_array.hello-world']);
        unset($this->dot['assoc_array.two']);
        unset($this->dot['assoc_array.no.key']);

        self::assertArrayNotHasKey('👋.🤘.some-key', $this->dot->get('mixed_array')->toArray());
        self::assertArrayNotHasKey('hello-world', $this->dot->get('mixed_array')->toArray());
        self::assertArrayNotHasKey('two', $this->dot->get('assoc_array')->toArray());

        self::assertArrayNotHasKey('👋.🤘.some-key', $this->dot['mixed_array']);
        self::assertArrayNotHasKey('hello-world', $this->dot['mixed_array']);
        self::assertArrayNotHasKey('two', $this->dot['assoc_array']);

        self::assertNotTrue(array_key_exists('👋.🤘.some-key', $this->dot['mixed_array']));
        self::assertNotTrue(array_key_exists('hello-world', $this->dot['mixed_array']));
        self::assertNotTrue(array_key_exists('two', $this->dot['assoc_array']));

        self::assertTrue(array_key_exists('one', $this->dot->get('assoc_array')->toArray()));
        self::assertTrue(array_key_exists('one', $this->dot['assoc_array']));
    }

    /**
     * Testing the Clear Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::clear
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testClear()
    {
        $this->dot->clear('assoc_array.two');
        $this->dot->clear(['assoc_array.one', 'assoc_array.three']);

        $users = $this->dot->get('mixed_array.users')->clear();

        self::assertIsArray($this->dot->get('assoc_array.one')->toArray());
        self::assertIsArray($this->dot->get('assoc_array.two')->toArray());
        self::assertIsArray($this->dot->get('assoc_array.three')->toArray());
        self::assertIsArray($users->toArray());

        self::assertEmpty($this->dot->get('assoc_array.one')->toArray());
        self::assertEmpty($this->dot->get('assoc_array.two')->toArray());
        self::assertEmpty($this->dot->get('assoc_array.three')->toArray());
        self::assertEmpty($users->toArray());
    }

    /**
     * Testing the Merge Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::merge
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testMerge()
    {
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

        $this->dot->merge(
            $extraData,
            [
                'new-entry' => [
                    'c' => new DotArray(
                        [
                            'd' => [1],
                        ]
                    ),
                ],
            ],
            [
                'new-entry' => [
                    'c' => new DotArray(
                        [
                            'd' => [1],
                        ]
                    ),
                ],
            ]
        );

        self::assertIsArray($this->dot->get('{new-entry}.a')->toArray());
        self::assertIsArray($this->dot->get('{new-entry}.b')->toArray());
        self::assertIsArray($this->dot->get('{new-entry}.b.c')->toArray());
        self::assertIsArray($this->dot->get('{new-entry}.b.c.e2')->toArray());

        self::assertEquals('new value for assoc_array.one.element_1', $this->dot->get('assoc_array.one.element_1'));
        self::assertEquals(['.other.config' => ['port' => 9300]], $this->dot->get('mixed_array.{👋.🤘.some-key}.config.{elastic-search}.{v6.0}')->toArray());
        self::assertCount(3, $this->dot->get('mixed_array.{👋.🤘.some-key}.config.memcached.servers'));
    }

    /**
     * Testing the Count Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::count
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testCount()
    {
        self::assertCount(2, $this->dot->get('mixed_array.{👋.🤘.some-key}.config.memcached.servers'));
        self::assertCount(0, $this->dot->get('empty_array.0'));
        self::assertCount(4, $this->dot->get('indexed_array'));
        self::assertCount(7, $this->dot['indexed_array'][0]);

        self::assertEquals(7, count($this->dot['indexed_array'][0]));
        self::assertEquals(1, count($this->dot['assoc_array']['three']));
    }

    /**
     * Testing the Find Method.
     *
     * @covers \BinaryCube\DotArray\DotFilteringTrait
     * @covers \BinaryCube\DotArray\DotArray::find
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testFind()
    {
        $admin = $this->dot->get('mixed_array.users')->find(
            function ($value) {
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

        self::assertFalse(
            $this->dot->find(
                function () {
                }
            )
        );
    }

    /**
     * Testing the Filter Method.
     *
     * @covers \BinaryCube\DotArray\DotFilteringTrait
     * @covers \BinaryCube\DotArray\DotArray::filter
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
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
     * Testing the FilterBy Method.
     *
     * @covers \BinaryCube\DotArray\DotFilteringTrait
     * @covers \BinaryCube\DotArray\DotArray::filterBy
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testFilterBy()
    {
        $user1 = [
            [
                'id' => 1,
                'name' => 'User 1',
            ],
        ];

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', '=', '1')->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', '===', 1)->toArray()
        );

        self::assertSame(
            $this->dot->get('mixed_array.users')->filter(
                function ($value) {
                    return $value['id'] !== 1;
                }
            )->toArray(),
            $this->dot->get('mixed_array.users')->filterBy('id', '!=', 1)->toArray()
        );

        self::assertSame(
            $this->dot->get('mixed_array.users')->filter(
                function ($value) {
                    return $value['id'] !== 1;
                }
            )->toArray(),
            $this->dot->get('mixed_array.users')->filterBy('id', '!==', 1)->toArray()
        );

        self::assertSame(
            $this->dot->get('mixed_array.users')->filter(
                function ($value) {
                    return $value['id'] !== 1;
                }
            )->toArray(),
            $this->dot->get('mixed_array.users')->filterBy('id', '>', 1)->toArray()
        );

        self::assertSame(
            $this->dot->get('mixed_array.users')->filter(
                function ($value) {
                    return $value['id'] >= 1;
                }
            )->toArray(),
            $this->dot->get('mixed_array.users')->filterBy('id', '>=', 1)->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', '<', 2)->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', '<=', 1)->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', 'in', [1])->toArray()
        );

        self::assertSame(
            $this->dot->get('mixed_array.users')->filter(
                function ($value) {
                    return $value['id'] !== 1;
                }
            )->toArray(),
            $this->dot->get('mixed_array.users')->filterBy('id', 'not-in', [1])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', 'between', 0, 1)->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', 'not-between', 2, PHP_INT_MAX)->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->filterBy('id', 'not-between', 2, PHP_INT_MAX)->toArray()
        );

        self::assertSame(
            [],
            $this->dot->get('mixed_array.users')->filterBy('no.key', '=', 1)->toArray()
        );
    }

    /**
     * Testing the Where Method.
     *
     * @covers \BinaryCube\DotArray\DotFilteringTrait
     * @covers \BinaryCube\DotArray\DotArray::where
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testWhere()
    {
        $user1 = [
            [
                'id' => 1,
                'name' => 'User 1',
            ],
        ];

        self::assertSame(
            $this->data,
            $this->dot->where(null)->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', '=', 1])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', '===', 1])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', '<', 2])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', '<=', 1])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', 'in', [1]])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', 'between', 0, 1])->toArray()
        );

        self::assertSame(
            $user1,
            $this->dot->get('mixed_array.users')->where(['id', 'not-between', 2, PHP_INT_MAX])->toArray()
        );

        $users = (
            $this->dot
                ->get('mixed_array')
                ->get('users')
                ->where(
                    function ($value) {
                        return ($value['id'] === 1);
                    }
                )
        );

        self::assertSame($user1, $users->toArray());
    }

    /**
     * Testing the First Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::first
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testFirst()
    {
        self::assertSame(
            $this->data['mixed_array']['👋.🤘.some-key'],
            $this->dot->get('mixed_array')->first()
        );
    }

    /**
     * Testing the toArray Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::toArray
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testToArray()
    {
        self::assertIsArray($this->dot->toArray());
        self::assertSame($this->data, $this->dot->toArray());
    }

    /**
     * Testing the toJson Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::toJson
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testToJson()
    {
        $dot            = DotArray::create($this->jsonArray);
        $jsonFromObject = $dot->toJson();
        $decode         = json_decode($jsonFromObject, true);

        self::assertSame($this->jsonString, $jsonFromObject);
        self::assertSame($this->jsonArray, $decode);
    }

    /**
     * Testing the toFlat Method.
     *
     * @covers \BinaryCube\DotArray\DotArray::flatten
     * @covers \BinaryCube\DotArray\DotArray::dotPathPattern
     * @covers \BinaryCube\DotArray\DotArray::wrapSegmentKey
     * @covers \BinaryCube\DotArray\DotArray::toFlat
     * @covers \BinaryCube\DotArray\DotArray::<protected>
     * @covers \BinaryCube\DotArray\DotArray::<static>
     *
     * @return void
     */
    public function testToFlat()
    {
        $dot = DotArray::create(
            [
                'a' => [
                    'b' => 'value',
                ],

                'b' => [
                    1,
                    2,
                    3,
                    'array' => [
                        1,
                        2,
                        3,
                    ]
                ],
            ]
        );

        self::assertSame(
            [
                '{a}.{b}' => 'value',
                '{b}.{0}' => 1,
                '{b}.{1}' => 2,
                '{b}.{2}' => 3,
                '{b}.{array}.{0}' => 1,
                '{b}.{array}.{1}' => 2,
                '{b}.{array}.{2}' => 3,
            ],
            $dot->toFlat()
        );
    }

    /**
     * Testing the serialize & unserialize Methods.
     *
     * @covers \BinaryCube\DotArray\DotArray::serialize
     * @covers \BinaryCube\DotArray\DotArray::unserialize
     *
     * @return void
     */
    public function testSerializable()
    {
        $serialize = $this->dot->serialize();

        $this->dot->unserialize($serialize);

        $unserialize = $this->dot;

        self::assertIsString($serialize);
        self::assertInstanceOf(DotArray::class, $unserialize);

        $serialize   = serialize($this->dot);
        $unserialize = unserialize($serialize);

        self::assertIsString($serialize);
        self::assertInstanceOf(DotArray::class, $unserialize);
    }

    /**
     * Testing the jsonSerialize Methods.
     *
     * @covers \BinaryCube\DotArray\DotArray::jsonSerialize
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        self::assertSame($this->data, $this->dot->jsonSerialize());
    }

    /**
     * Testing the getIterator Methods.
     *
     * @covers \BinaryCube\DotArray\DotArray::getIterator
     *
     * @return void
     */
    public function testIterator()
    {

        self::assertInstanceOf(\ArrayIterator::class, $this->dot->getIterator());
        self::assertSame($this->data, $this->dot->getIterator()->getArrayCopy());
    }

}
