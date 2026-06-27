<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Standard;

final class ArrayStandard
{
    // @phpstan-ignore-next-line
    public function __construct(
        private readonly array $array
    ) {
    }

    // @phpstan-ignore-next-line
    public function filterBoth(?callable $callback = null): array
    {
        $array = $this->array;
        $callable = $callback ?? __NAMESPACE__ . '\notEmpty';
        $function = __NAMESPACE__ . '\arrayFilterBoth';
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = call_user_func($function, $value, $callable);
            }
            if (! $callable($value, $key)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    // @phpstan-ignore-next-line
    public function filterValue(?callable $callback = null): array
    {
        $array = $this->array;
        $callable = $callback ?? __NAMESPACE__ . '\notEmpty';
        $function = __NAMESPACE__ . '\arrayFilterValue';
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = call_user_func($function, $value, $callable);
            }
            $notEmptyArray = is_array($value) && $value !== [];
            $response = $callable($value) ?: $notEmptyArray;
            if (! $response) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    // @phpstan-ignore-next-line
    public function filterKey(?callable $callback = null): array
    {
        $array = $this->array;
        $callable = $callback ?? __NAMESPACE__ . '\notEmpty';
        $function = __NAMESPACE__ . '\arrayFilterKey';
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = call_user_func($function, $value, $callable);
            }
            if (! $callable($key)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    // @phpstan-ignore-next-line
    public function changeKey(string|int ...$key): array
    {
        $array = $this->array;
        foreach ($key as $search => $replace) {
            $search = strval($search);
            if (! array_key_exists($search, $array)) {
                continue;
            }
            $array[$replace] = $array[$search];
            unset($array[$search]);
        }

        return $array;
    }

    // @phpstan-ignore-next-line
    public function changeValue(mixed $search, mixed $replace): array
    {
        $array = $this->array;
        $keys = array_keys($array, $search, true);
        foreach ($keys as $key) {
            $array[$key] = $replace;
        }

        return $array;
    }

    // @phpstan-ignore-next-line
    public function prefixKeys(string|int $prefix): array
    {
        $array = $this->array;
        $return = [];
        foreach ($array as $key => $value) {
            $return[$prefix . $key] = $value;
            unset($array[$key]);
        }

        return $return;
    }

    // @phpstan-ignore-next-line
    public function unsetKey(string|int ...$key): array
    {
        $array = $this->array;
        foreach ($key as $name) {
            if (array_key_exists($name, $array)) {
                unset($array[$name]);
            }
        }

        return $array;
    }

    // @phpstan-ignore-next-line
    public function fromKey(string|int ...$key): array
    {
        $array = $this->array;
        $return = [];
        foreach ($key as $name) {
            if (array_key_exists($name, $array)) {
                $return[$name] = $array[$name];
            }
        }

        return $return;
    }

    // @phpstan-ignore-next-line
    public function prefixValues(string|int|float $prefix): array
    {
        $array = $this->array;

        return array_map(fn ($value) => $prefix . $value, $array);
    }

    // @phpstan-ignore-next-line
    public function suffixValues(string|int|float $suffix): array
    {
        $array = $this->array;

        return array_map(fn ($value) => $value . $suffix, $array);
    }
}
