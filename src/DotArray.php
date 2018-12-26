<?php

namespace BinaryCube\DotArray;

/**
 * DotArray
 *
 * @package BinaryCube\DotArray
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
class DotArray implements
    \ArrayAccess,
    \IteratorAggregate,
    \Serializable,
    \JsonSerializable,
    \Countable
{

    /**
     * Unique object identifier.
     *
     * @var string
     */
    protected $uniqueIdentifier;

    /**
     * Config.
     *
     * @var array
     */
    protected $config = [
        'path' => [
            'template'  => '#(?|(?|[<token-start>](.*?)[<token-end>])|(.*?))(?:$|\.+)#i',
            'wildcards' => [
                '<token-start>' => ['\'', '\"', '\[', '\(', '\{'],
                '<token-end>'   => ['\'', '\"', '\]', '\)', '\}'],
            ],
        ],
    ];

    /**
     * The pattern that allow to match the JSON paths that use the dot notation.
     *
     * Allowed tokens for more complex paths: '', "", [], (), {}
     * Examples:
     *
     * - foo.bar
     * - foo.'bar'
     * - foo."bar"
     * - foo.[bar]
     * - foo.(bar)
     * - foo.{bar}
     *
     * Or more complex:
     * - foo.{bar}.[component].{version.1.0}
     *
     * @var string
     */
    protected $nestedPathPattern;

    /**
     * Stores the original data.
     *
     * @var array
     */
    protected $items;


    /**
     * Creates an DotArray object.
     *
     * @param mixed $items
     *
     * @return static
     */
    public static function create($items)
    {
        return (new static($items));
    }


    /**
     * @param string $json
     *
     * @return static
     */
    public static function createFromJson($json)
    {
        return static::create(\json_decode($json, true));
    }


    /**
     * Return the given items as an array
     *
     * @param mixed $items
     *
     * @return array
     */
    protected static function normalize(&$items)
    {
        if (\is_array($items)) {
            return $items;
        } else if (empty($items)) {
            return [];
        } else if ($items instanceof self) {
            return $items->toArray();
        }

        return (array) $items;
    }


    /**
     * List with internal operators and the associated callbacks.
     *
     * @return array
     */
    protected static function operators()
    {
        return [
            [
                'tokens' => ['=', '==', 'eq'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] == $value[0];
                },
            ],

            [
                'tokens' => ['===', 'i'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] === $value[0];
                },
            ],

            [
                'tokens' => ['!=', 'ne'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] != $value[0];
                },
            ],

            [
                'tokens' => ['!==', 'ni'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] !== $value[0];
                },
            ],

            [
                'tokens' => ['<', 'lt'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] < $value[0];
                },
            ],

            [
                'tokens' => ['>', 'gt'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] > $value[0];
                },
            ],

            [
                'tokens' => ['<=', 'lte'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] <= $value[0];
                },
            ],

            [
                'tokens' => ['>=', 'gte'],
                'closure' => function ($item, $property, $value) {
                    return $item[$property] >= $value[0];
                },
            ],

            [
                'tokens' => ['in', 'contains'],
                'closure' => function ($item, $property, $value) {
                    return \in_array($item[$property], (array) $value, true);
                },
            ],

            [
                'tokens' => ['not-in', 'not-contains'],
                'closure' => function ($item, $property, $value) {
                    return !\in_array($item[$property], (array) $value, true);
                },
            ],

            [
                'tokens' => ['between'],
                'closure' => function ($item, $property, $value) {
                    return ($item[$property] >= $value[0] && $item[$property] <= $value[1]);
                },
            ],

            [
                'tokens' => ['not-between'],
                'closure' => function ($item, $property, $value) {
                    return ($item[$property] < $value[0] || $item[$property] > $value[1]);
                },
            ],
        ];
    }


    /**
     * DotArray Constructor.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = static::normalize($items);
    }


    /**
     * DotArray Destructor.
     */
    public function __destruct()
    {
        unset($this->uniqueIdentifier);
        unset($this->items);
        unset($this->nestedPathPattern);
    }


    /**
     * Call object as function.
     *
     * @param null|string $key
     *
     * @return mixed|static
     */
    public function __invoke($key = null)
    {
        return $this->get($key);
    }


    /**
     * @return string
     */
    public function uniqueIdentifier()
    {
        if (empty($this->uniqueIdentifier)) {
            $this->uniqueIdentifier = vsprintf(
                '{%s}.{%s}.{%s}',
                [
                    static::class,
                    \uniqid('', true),
                    microtime(true),
                ]
            );
        }

        return $this->uniqueIdentifier;
    }


    /**
     * Getting the nested path pattern.
     *
     * @return string
     */
    protected function nestedPathPattern()
    {
        if (empty($this->nestedPathPattern)) {
            $path = $this->config['path']['template'];

            foreach ($this->config['path']['wildcards'] as $wildcard => $tokens) {
                $path = \str_replace($wildcard, \implode('', $tokens), $path);
            }

            $this->nestedPathPattern = $path;
        }

        return $this->nestedPathPattern;
    }


    /**
     * Converts dot string path to segments.
     *
     * @param string $path
     *
     * @return array
     */
    protected function pathToSegments($path)
    {
        $path     = \trim($path, " \t\n\r\0\x0B\.");
        $segments = [];
        $matches  = [];

        \preg_match_all($this->nestedPathPattern(), $path, $matches);

        if (!empty($matches[1])) {
            $matches = $matches[1];

            $segments = \array_filter(
                $matches,
                function ($match) {
                    return (\mb_strlen($match, 'UTF-8') > 0);
                }
            );
        }

        unset($matches);

        return (empty($segments) ? [] : $segments);
    }


    /**
     * Wrap a given string into special characters.
     *
     * @param string $key
     *
     * @return string
     */
    protected function wrapSegmentKey($key)
    {
        return "{{~$key~}}";
    }


    /**
     * @param array $segments
     *
     * @return string
     */
    protected function segmentsToKey(array $segments)
    {
        return (
            \implode(
                '',
                \array_map(
                    [$this, 'wrapSegmentKey'],
                    $segments
                )
            )
        );
    }


    /**
     * @param array|DotArray|mixed      $array1
     * @param null|array|DotArray|mixed $array2
     *
     * @return array
     */
    protected static function mergeRecursive($array1, $array2 = null)
    {
        $args = \func_get_args();
        $res  = \array_shift($args);

        while (!empty($args)) {
            foreach (\array_shift($args) as $k => $v) {
                if ($v instanceof self) {
                    $v = $v->toArray();
                }

                if (\is_int($k)) {
                    if (\array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } else if (
                    \is_array($v)
                    && isset($res[$k])
                    && \is_array($res[$k])
                ) {
                    $res[$k] = static::mergeRecursive($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }//end foreach
        }//end while

        return $res;
    }


    /**
     * Merges one or more arrays into master recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     *
     * @param array|DotArray|mixed $array Array to be merged from. You can specify additional
     *                                    arrays via second argument, third argument, fourth argument etc.
     *
     * @return static
     */
    public function merge($array)
    {
        $this->items = \call_user_func_array(
            [
                $this, 'mergeRecursive',
            ],
            \array_merge(
                [$this->items],
                \func_get_args()
            )
        );

        return $this;
    }


    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return array|mixed
     */
    protected function &read($key, $default)
    {
        $segments = $this->pathToSegments($key);
        $items    = &$this->items;

        foreach ($segments as $segment) {
            if (
                !\is_array($items)
                || !\array_key_exists($segment, $items)
            ) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }


    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    protected function write($key, $value)
    {
        $segments = $this->pathToSegments($key);
        $count    = \count($segments);
        $items    = &$this->items;

        for ($i = 0; $i < $count; $i++) {
            $segment = $segments[$i];

            if (
                (
                    !isset($items[$segment])
                    || !\is_array($items[$segment])
                )
                && ($i < ($count - 1))
            ) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        $items = $value;
    }


    /**
     * Delete the given key or keys.
     *
     * @param string $key
     *
     * @return void
     */
    protected function remove($key)
    {
        $segments = $this->pathToSegments($key);
        $count    = \count($segments);
        $items    = &$this->items;

        for ($i = 0; $i < $count; $i++) {
            $segment = $segments[$i];

            // Nothing to unset.
            if (!\array_key_exists($segment, $items)) {
                break;
            }

            // Last item, time to unset.
            if ($i === ($count - 1)) {
                unset($items[$segment]);
                break;
            }

            $items = &$items[$segment];
        }
    }


    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return ($this->read($key, $this->uniqueIdentifier()) !== $this->uniqueIdentifier());
    }


    /**
     * Check if a given key is empty.
     *
     * @param null|string $key
     *
     * @return bool
     */
    public function isEmpty($key = null)
    {
        if (!isset($key)) {
            return empty($this->items);
        }

        $items = $this->read($key, null);

        if ($items instanceof self) {
            $items = $items->toArray();
        }

        return empty($items);
    }


    /**
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return mixed|static
     */
    public function get($key = null, $default = null)
    {
        $items = $this->read($key, $default);

        if (\is_array($items)) {
            $items = static::create($items);
        }

        return $items;
    }


    /**
     * Set the given value to the provided key or keys.
     *
     * @param string|array $keys
     * @param mixed        $value
     *
     * @return static
     */
    public function set($keys, $value)
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $this->write($key, $value);
        }

        return $this;
    }


    /**
     * Delete the given key or keys.
     *
     * @param string|array $keys
     *
     * @return static
     */
    public function delete($keys)
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            $this->remove($key);
        }

        return $this;
    }


    /**
     * Set the contents of a given key or keys to the given value (default is empty array).
     *
     * @param null|string|array $keys
     * @param array             $value
     *
     * @return static
     */
    public function clear($keys = null, $value = [])
    {
        $keys = (array) (!isset($keys) ? [$keys] : $keys);

        foreach ($keys as $key) {
            $this->write($key, $value);
        }

        return $this;
    }


    /**
     * Find the first item in an array that passes the truth test, otherwise return false
     * The signature of the callable must be: `function ($value, $key)`
     *
     * @param \Closure $closure
     *
     * @return false|mixed
     */
    public function find(\Closure $closure)
    {
        foreach ($this->items as $key => $value) {
            if ($closure($value, $key)) {
                if (\is_array($value)) {
                    $value = static::create($value);
                }

                return $value;
            }
        }

        return false;
    }


    /**
     * Use a callable function to filter through items.
     * The signature of the callable must be: `function ($value, $key)`
     *
     * @param \Closure|null $closure
     * @param int           $flag    Flag determining what arguments are sent to callback.
     *                               ARRAY_FILTER_USE_KEY :: pass key as the only argument
     *                               to callback.
     *                               ARRAY_FILTER_USE_BOTH :: pass both value
     *                               and key as arguments to callback.
     *
     * @return static
     */
    public function filter(\Closure $closure = null, $flag = ARRAY_FILTER_USE_BOTH)
    {
        $items = $this->items;

        if (!isset($closure)) {
            return static::create($items);
        }

        return (
            static::create(
                \array_values(
                    \array_filter(
                        $items,
                        $closure,
                        $flag
                    )
                )
            )
        );
    }


    /**
     * Allow to filter an array using one of the following comparison operators:
     *  - [ =, ==, eq (equal) ]
     *  - [ ===, i (identical) ]
     *  - [ !=, ne (not equal) ]
     *  - [ !==, ni (not identical) ]
     *  - [ <, lt (less than) ]
     *  - [ >, gr (greater than) ]
     *  - [ <=, lte (less than or equal to) ]
     *  - [ =>, gte (greater than or equal to) ]
     *  - [ in, contains ]
     *  - [ not-in, not-contains ]
     *  - [ between ]
     *  - [ not-between ]
     *
     * @param string $property
     * @param string $comparisonOperator
     * @param mixed  $value
     *
     * @return static
     */
    public function filterBy($property, $comparisonOperator, $value)
    {
        $args  = \func_get_args();
        $value = (array) (array_slice($args, 2, count($args)));

        $closure   = null;
        $operators = static::operators();

        if (isset($value[0]) && \is_array($value[0])) {
            $value = $value[0];
        }

        foreach ($operators as $entry) {
            if (\in_array($comparisonOperator, $entry['tokens'])) {
                $closure = function ($item) use ($entry, $property, $value) {
                    $item = (array) $item;

                    if (!array_key_exists($property, $item)) {
                        return false;
                    }

                    return $entry['closure']($item, $property, $value);
                };

                break;
            }
        }

        return $this->filter($closure);
    }


    /**
     * Filtering through array.
     * The signature of the call can be:
     * - where([property, comparisonOperator, ...value])
     * - where(\Closure) :: The signature of the callable must be: `function ($value, $key)`
     * - where([\Closure]) :: The signature of the callable must be: `function ($value, $key)`
     *
     * Allowed comparison operators:
     *  - [ =, ==, eq (equal) ]
     *  - [ ===, i (identical) ]
     *  - [ !=, ne (not equal) ]
     *  - [ !==, ni (not identical) ]
     *  - [ <, lt (less than) ]
     *  - [ >, gr (greater than) ]
     *  - [ <=, lte (less than or equal to) ]
     *  - [ =>, gte (greater than or equal to) ]
     *  - [ in, contains ]
     *  - [ not-in, not-contains ]
     *  - [ between ]
     *  - [ not-between ]
     *
     * @param array|callable $criteria
     *
     * @return static
     */
    public function where($criteria)
    {
        $criteria = (array) $criteria;

        if (empty($criteria)) {
            return $this->filter();
        }

        $closure = \array_shift($criteria);

        if ($closure instanceof \Closure) {
            return $this->filter($closure);
        }

        $property           = $closure;
        $comparisonOperator = \array_shift($criteria);
        $value              = $criteria;

        if (isset($value[0]) && \is_array($value[0])) {
            $value = $value[0];
        }

        return $this->filterBy($property, $comparisonOperator, $value);
    }


    /**
     * Returning the first value from the current array.
     *
     * @return mixed
     */
    public function first()
    {
        $items = $this->items;

        return \array_shift($items);
    }


    /**
     * Whether a offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *
     * The return value will be casted to boolean if non-boolean was returned.
     * @since  5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }


    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     *
     * @since 5.0.0
     */
    public function &offsetGet($offset)
    {
        return $this->read($offset, null);
    }


    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->write($offset, $value);
    }


    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (\array_key_exists($offset, $this->items)) {
            unset($this->items[$offset]);
            return;
        }

        $this->remove($offset);
    }


    /**
     * Count elements of an object
     *
     * @link https://php.net/manual/en/countable.count.php
     *
     * @param int $mode
     *
     * @return int The custom count as an integer.
     *
     * @since 5.1.0
     */
    public function count($mode = COUNT_NORMAL)
    {
        return \count($this->items, $mode);
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }


    /**
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return \json_encode($this->items, $options);
    }


    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->items;
    }


    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     *
     * @since 5.1.0
     */
    public function serialize()
    {
        return \serialize($this->items);
    }


    /**
     * Constructs the object
     *
     * @link https://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized The string representation of the object.

     * @return void
     *
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->items = \unserialize($serialized);
    }


    /**
     * Retrieve an external iterator.
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return \ArrayIterator An instance of an object implementing Iterator or Traversable
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }


}
