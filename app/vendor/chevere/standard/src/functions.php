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

/**
 * @return array<int> Bits (powers of two)
 */
function getBits(int $value): array
{
    $return = [];
    $bit = 1;
    while ($bit <= $value) {
        if ($bit & $value) {
            $return[] = $bit;
        }
        $bit <<= 1;
    }

    return $return;
}

function notEmpty(mixed $value): bool
{
    return ! empty($value);
}

/**
 * @phpstan-ignore-next-line
 */
function arrayFilterBoth(array $array, ?callable $callback = null): array
{
    return (new ArrayStandard($array))->filterBoth($callback);
}

/**
 * @phpstan-ignore-next-line
 */
function arrayFilterValue(array $array, ?callable $callback = null): array
{
    return (new ArrayStandard($array))->filterValue($callback);
}

/**
 * @phpstan-ignore-next-line
 */
function arrayFilterKey(array $array, ?callable $callback = null): array
{
    return (new ArrayStandard($array))->filterKey($callback);
}

/**
 * @param string|int $key The key(s) to change (name: change,)
 * @phpstan-ignore-next-line
 */
function arrayChangeKey(array $array, string|int ...$key): array
{
    return (new ArrayStandard($array))->changeKey(...$key);
}

/**
 * @phpstan-ignore-next-line
 */
function arrayChangeValue(array $array, mixed $search, mixed $replace = null): array
{
    return (new ArrayStandard($array))->changeValue($search, $replace);
}

/**
 * @phpstan-ignore-next-line
 */
function arrayPrefixKeys(array $array, string|int $prefix): array
{
    return (new ArrayStandard($array))->prefixKeys($prefix);
}

/**
 * @phpstan-ignore-next-line
 */
function arrayPrefixValues(array $array, string|int|float $prefix): array
{
    return (new ArrayStandard($array))->prefixValues($prefix);
}

/**
 * @phpstan-ignore-next-line
 */
function arraySuffixValues(array $array, string|int|float $suffix): array
{
    return (new ArrayStandard($array))->suffixValues($suffix);
}

/**
 * @param string|int $key Key(s) to unset.
 * @phpstan-ignore-next-line
 */
function arrayUnsetKey(array $array, string|int ...$key): array
{
    return (new ArrayStandard($array))->unsetKey(...$key);
}

/**
 * @param string|int $key Key(s) to take.
 * @phpstan-ignore-next-line
 */
function arrayFromKey(array $array, string|int ...$key): array
{
    return (new ArrayStandard($array))->fromKey(...$key);
}

/**
 * Takes packing instruction and generates sub-arrays with matched keys.
 *
 * @param array $array Array to pack (contains keys starting with needle)
 * @param string ...$packing Named packing as `startNeedle: 'grouping',`
 * @phpstan-ignore-next-line
 */
function arrayPack(array $array, string ...$packing): array
{
    $trash = [];
    $keys = array_keys($array);
    foreach ($packing as $split => $group) {
        $split = (string) $split;
        $find = array_filter($keys, function ($key) use ($split) {
            return strpos($key, $split) === 0;
        });
        if ($find === []) {
            continue;
        }
        $array[$group] = [];
        foreach ($find as $key) {
            $key = (string) $key;
            $trash[] = $key;
            $groupedKey = (string) str_replace($split, '', $key);
            $array[$group][$groupedKey] = $array[$key];
        }
    }

    return arrayUnsetKey($array, ...$trash);
}

function randomString(int $length): string
{
    // @phpstan-ignore-next-line
    $randomBytes = random_bytes($length);

    return substr(bin2hex($randomBytes), 0, $length);
}

function uuidV4(): string
{
    $data = random_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

    return vsprintf(
        '%s%s-%s-%s-%s-%s%s%s',
        str_split(bin2hex($data), 4)
    );
}
