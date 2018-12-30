<?php

namespace BinaryCube\DotArray;

/**
 * DotPathTrait
 *
 * @package BinaryCube\DotArray
 * @author  Banciu N. Cristian Mihai <banciu.n.cristian.mihai@gmail.com>
 * @license https://github.com/binary-cube/dot-array/blob/master/LICENSE <MIT License>
 * @link    https://github.com/binary-cube/dot-array
 */
trait DotPathTrait
{

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

        return $segments;
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


}
