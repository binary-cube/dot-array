<?php

namespace BinaryCube\DotArray;

/**
 * DotFilteringTrait
 *
 * @package BinaryCube\DotArray
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
trait DotFilteringTrait
{

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
     * Find the first item in an array that passes the truth test, otherwise return false.
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
     *                               to callback. ARRAY_FILTER_USE_BOTH :: pass both value
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

        return static::create(
            \array_values(
                \array_filter(
                    $items,
                    $closure,
                    $flag
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
        $value = \array_slice($args, 2, \count($args));

        $closure   = null;
        $operators = static::operators();

        if (isset($value[0]) && \is_array($value[0])) {
            $value = $value[0];
        }

        foreach ($operators as $entry) {
            if (\in_array($comparisonOperator, $entry['tokens'])) {
                $closure = function ($item) use ($entry, $property, $value) {
                    $item = (array) $item;

                    if (!\array_key_exists($property, $item)) {
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

}
