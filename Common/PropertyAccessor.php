<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Common;

/**
 * @version 1.0.0
 * Utility for accesing nested arrays
 */
abstract class PropertyAccessor
{
    public const RETURN_ENUM = 1;
    public static function get($object, string $property = '', int $options = 0)
    {
        $props = static::extractProperties($property);
        if (empty($props)) {
            return $object;
        }

        $result = $object;
        foreach ($props as $key) {
            if (empty($result) || !is_array($result) || !\array_key_exists($key, $result)) {
                if ($options & static::RETURN_ENUM) {
                    return PropertyAccess::NOT_FOUND;
                }
                break;
            }
            $result = $result[$key];
        }
        if (null === $result && ($options & static::RETURN_ENUM)) {
            return PropertyAccess::IS_NULL;
        }
        return $result;
    }

    public static function removeByValue(array &$array, $deleteValue, int $options = 0)
    {
        foreach ($array as $i => $value) {
            if ($value === $deleteValue) {
                unset($array[$i]);
                return $i;
            }
        }
    }
    private static function extractProperties(string $propertyString): array
    {
        $part = '';
        $parts = [];
        $stringLength = \strlen($propertyString);
        $escape = false;
        $collect = false;
        foreach (\str_split($propertyString) as $i => $char) {
            $last = $stringLength - 1 === $i ? true : false;
            // finish part when not hitting ']' or dot at the end of the string + that char
            if (!((']' === $char || '.' === $char) && false === $escape) && true === $collect && true === $last) {
                $parts[] = $part . $char;
                $part = '';
                $collect = false;
            }
            // finish part when hitting ']' or dot or string ends completely without ']'
            if (
                (']' === $char && true === $collect && false === $escape)
                || ('.' === $char && true === $collect && false === $escape)
                || ('[' === $char && true === $collect && false === $escape)
            ) {
                $parts[] = $part;
                $part = '';
                $collect = false;
            }
            if (true === $collect) {
                $part .= $char;
            }
            // start collecting chars next char
            if (('[' === $char || '.' === $char) && false === $escape) {
                $collect = true;
            }
            $escape = '\\' === $char ? true : false;
        }

        return \str_replace(["\[", "\]", "\."], ["[", "]", "."], $parts);
    }
}
