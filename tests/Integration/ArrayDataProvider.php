<?php

namespace BinaryCube\DotArray\Tests\Integration;

/**
 * ArrayDataProvider
 *
 * @package BinaryCube\DotArray\Tests
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
class ArrayDataProvider
{


    /**
     * @return array
     */
    public static function get()
    {
        $array = [
            'empty_array' =>
                [
                    [],
                ],

            'indexed_array' =>
                [
                    0 => [
                        0 => '0.0',
                        1 => '0.1',
                        2 => '0.2',
                        3 => '0.3',
                        5 => '0.5',
                        8 => '0.8',
                        13 => '0.13',
                    ],
                    1 => [
                        0 => '1.0',
                        1 => '1.1',
                        2 => '1.2',
                        3 => '1.3',
                        5 => '1.5',
                        8 => '1.8',
                        13 => '1.13',
                    ],
                    2 => [
                        0 => '2.0',
                        1 => '2.1',
                        2 => '2.2',
                        3 => '2.3',
                        5 => '2.5',
                        8 => '2.8',
                        13 => '2.13',
                    ],
                    3 => [
                        0 => '3.0',
                        1 => '3.1',
                        2 => '3.2',
                        3 => '3.3',
                        5 => '3.5',
                        8 => '3.8',
                        13 => '3.13',
                    ],
                ],

            'assoc_array' =>
                [
                    'one' => [
                        'element_1' => '1',
                        'element.2' => '2',
                        'element->3' => '3',
                    ],
                    'two' => [
                        '1.element.a' => '1.a',
                        'a.element.1' => 'a.1',
                        'a.b.c' => '🤘',
                    ],
                    'three' => [
                        '👋' => 'smile and wave boys',
                    ],
                ],

            'mixed_array' =>
                [
                    '👋.🤘.some-key' => [
                        'config' => [
                            'elastic-search' => [
                                'v5.0' => [
                                    '...some-config' => [
                                        'port' => 9200,
                                    ],
                                ],

                                'v6.0' => [
                                    '.other.config' => [
                                        'port' => 9200,
                                    ],
                                ],
                            ],

                            'memcached' => [
                                'servers' => [
                                    [
                                        'host' => '127.0.0.1',
                                        'port' => '12345',
                                    ],

                                    [
                                        'host' => '127.0.0.1',
                                        'port' => '12346',
                                    ],
                                ],
                            ]
                        ],
                    ],

                    'hello-world' => [
                        'Romanian' => 'Salut',
                        'Portuguese' => 'Olá',
                        'Chinese' => 'Nǐ hǎo',
                        'Swedish' => 'Hallå',

                        'Salut' => 'Romanian',
                        'Olá' => 'Portuguese',
                        'Nǐ hǎo' => 'Chinese',
                        'Hallå' => 'Swedish',
                    ],

                    'users' => [
                        [
                            'id' => 1,
                            'name' => 'User 1',
                        ],

                        [
                            'id' => 2,
                            'name' => 'User 2',
                        ],

                        [
                            'id' => 3,
                            'name' => 'User 3',
                        ],

                        [
                            'id' => 4,
                            'name' => 'User 4',
                        ],

                        [
                            'id' => 5,
                            'name' => 'User 5',
                            'is_admin' => true,
                        ],
                    ]
                ]
        ];

        return $array;
    }


}
