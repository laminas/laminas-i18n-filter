<?php

declare(strict_types=1);

namespace Laminas\I18n\Filter\Internal;

use Closure;
use Stringable;

use function array_map;
use function is_array;
use function is_float;
use function is_int;
use function is_string;

/**
 * This class is internal and as such is not subject to any backwards compatibility guarantees.
 *
 * @internal
 *
 * @psalm-internal \Laminas\I18n\Filter
 * @psalm-internal \LaminasTest\I18n\Filter
 */
final class ScalarOrArrayFilterCallback
{
    /**
     * Recursively applies a callback to an array of scalars or scalar input. Non-scalar values are skipped.
     *
     * @template T
     * @param T $value
     * @param Closure(string): string $callback
     * @return T|string|array<array-key, string|mixed>
     */
    public static function applyRecursively(mixed $value, Closure $callback): mixed
    {
        if (is_int($value) || is_float($value) || is_string($value) || $value instanceof Stringable) {
            return $callback((string) $value);
        }

        if (is_array($value)) {
            return array_map(
                static fn (mixed $value): mixed => self::applyRecursively($value, $callback),
                $value,
            );
        }

        return $value;
    }
}
