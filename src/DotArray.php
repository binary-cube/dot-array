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

    /* Traits. */
    use DotFilteringTrait;

    /**
     * Internal Dot Path Config.
     *
     * @var array
     */
    protected static $dotPathConfig = [
        'template'  => '#(?|(?|[<token-start>](.*?)[<token-end>])|(.*?))(?:$|\.+)#i',
        'wrapKey'   => '{%s}',
        'wildcards' => [
            '<token-start>' => ['\'', '\"', '\[', '\(', '\{'],
            '<token-end>'   => ['\'', '\"', '\]', '\)', '\}'],
        ],
    ];

    /**
     * The cached pattern that allow to match the JSON paths that use the dot notation.
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
    protected static $dotPathPattern;

    /**
     * Unique object identifier.
     *
     * @var string
     */
    protected $uniqueIdentifier;

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
     * Getting the dot path pattern.
     *
     * @return string
     */
    protected static function dotPathPattern()
    {
        if (empty(self::$dotPathPattern)) {
            $path = self::$dotPathConfig['template'];

            foreach (self::$dotPathConfig['wildcards'] as $wildcard => $tokens) {
                $path = \str_replace($wildcard, \implode('', $tokens), $path);
            }

            self::$dotPathPattern = $path;
        }

        return self::$dotPathPattern;
    }

    /**
     * Converts dot string path to segments.
     *
     * @param string $path
     *
     * @return array
     */
    protected static function pathToSegments($path)
    {
        $path     = \trim($path, " \t\n\r\0\x0B\.");
        $segments = [];
        $matches  = [];

        if (\mb_strlen($path, 'UTF-8') === 0) {
            return [];
        }

        \preg_match_all(static::dotPathPattern(), $path, $matches);

        if (!empty($matches[1])) {
            $matches = $matches[1];

            $segments = \array_filter(
                $matches,
                function ($match) {
                    return (\mb_strlen($match, 'UTF-8') > 0);
                }
            );
        }

        unset($path, $matches);

        return (empty($segments) ? [] : $segments);
    }

    /**
     * Wrap a given string into special characters.
     *
     * @param string $key
     *
     * @return string
     */
    protected static function wrapSegmentKey($key)
    {
        return vsprintf(static::$dotPathConfig['wrapKey'], [$key]);
    }

    /**
     * @param array $segments
     *
     * @return string
     */
    protected static function segmentsToKey(array $segments)
    {
        return (
        \implode(
            '.',
            \array_map(
                function ($segment) {
                    return static::wrapSegmentKey($segment);
                },
                $segments
            )
        )
        );
    }

    /**
     * Flatten the internal array using the dot delimiter,
     * also the keys are wrapped inside {key} (1 x curly braces).
     *
     * @param array $items
     * @param array $prepend
     *
     * @return array
     */
    protected static function flatten(array $items, $prepend = [])
    {
        $flatten = [];

        foreach ($items as $key => $value) {
            if (\is_array($value) && !empty($value)) {
                $flatten = array_merge(
                    $flatten,
                    static::flatten(
                        $value,
                        array_merge($prepend, [$key])
                    )
                );

                continue;
            }

            $segmentsToKey = static::segmentsToKey(array_merge($prepend, [$key]));

            $flatten[$segmentsToKey] = $value;
        }

        return $flatten;
    }

    /**
     * Return the given items as an array
     *
     * @param mixed $items
     *
     * @return array
     */
    protected static function normalize($items)
    {
        if ($items instanceof self) {
            $items = $items->toArray();
        }

        if (\is_array($items)) {
            foreach ($items as $k => $v) {
                if (\is_array($v) || $v instanceof self) {
                    $v = static::normalize($v);
                }
                $items[$k] = $v;
            }
        }

        return (array) $items;
    }

    /**
     * @param array|DotArray|mixed      $array1
     * @param null|array|DotArray|mixed $array2
     *
     * @return array
     */
    protected static function mergeRecursive($array1, $array2 = null)
    {
        $args = static::normalize(\func_get_args());
        $res  = \array_shift($args);

        while (!empty($args)) {
            foreach (\array_shift($args) as $k => $v) {
                if (\is_int($k) && \array_key_exists($k, $res)) {
                    $res[] = $v;
                    continue;
                }

                if (\is_array($v) && isset($res[$k]) && \is_array($res[$k])) {
                    $v = static::mergeRecursive($res[$k], $v);
                }

                $res[$k] = $v;
            }
        }

        return $res;
    }

    /**
     * DotArray Constructor.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = static::normalize($items);

        $this->uniqueIdentifier();
    }

    /**
     * DotArray Destructor.
     */
    public function __destruct()
    {
        unset($this->uniqueIdentifier);
        unset($this->items);
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
            $this->uniqueIdentifier = static::segmentsToKey(
                [
                    static::class,
                    \uniqid('', true),
                    \microtime(true),
                ]
            );
        }

        return $this->uniqueIdentifier;
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
            \array_values(
                \array_merge(
                    [$this->items],
                    \func_get_args()
                )
            )
        );

        return $this;
    }

    /**
     * @param string|null|mixed $key
     * @param mixed             $default
     *
     * @return array|mixed
     */
    protected function &read($key = null, $default = null)
    {
        $segments = static::pathToSegments($key);
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

        unset($segments);

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
        $segments = static::pathToSegments($key);
        $count    = \count($segments);
        $items    = &$this->items;

        for ($i = 0; $i < $count; $i++) {
            $segment = $segments[$i];

            if (
                (!isset($items[$segment]) || !\is_array($items[$segment]))
                && ($i < ($count - 1))
            ) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        if (\is_array($value) || $value instanceof self) {
            $value = static::normalize($value);
        }

        $items = $value;

        if (!\is_array($this->items)) {
            $this->items = static::normalize($this->items);
        }
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
        $segments = static::pathToSegments($key);
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
        $identifier = $this->uniqueIdentifier();

        return ($identifier !== $this->read($key, $identifier));
    }

    /**
     * Check if a given key contains empty values (null, [], 0, false)
     *
     * @param null|string $key
     *
     * @return bool
     */
    public function isEmpty($key = null)
    {
        $items = $this->read($key, null);

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
     * @param null|string|array $keys
     * @param mixed|mixed       $value
     *
     * @return static
     */
    public function set($keys = null, $value = [])
    {
        $keys = (array) (!isset($keys) ? [$keys] : $keys);

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
     * @param array|mixed       $value
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

    /**
     * Getting the internal raw array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Getting the internal raw array as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return (string) \json_encode($this->items, $options);
    }

    /**
     * Flatten the internal array using the dot delimiter,
     * also the keys are wrapped inside {key} (1 x curly braces).
     *
     * @return array
     */
    public function toFlat()
    {
        return static::flatten($this->items);
    }

}
