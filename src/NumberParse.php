<?php

declare(strict_types=1);

namespace Laminas\I18n\Filter;

use IntlException;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\FilterInterface;
use NumberFormatter;
use Stringable;

use function assert;
use function is_string;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

/**
 * A filter that parses localised numbers to integer or float values from arbitrary strings
 *
 * @psalm-type Options = array{
 *    locale?: non-empty-string,
 *    style?: int,
 *    type?: NumberFormatter::TYPE_*,
 * }
 * @implements FilterInterface<int|float>
 */
final readonly class NumberParse implements FilterInterface
{
    private NumberFormatter $formatter;
    /** @var NumberFormatter::TYPE_* */
    private int $type;

    /** @param Options $options */
    public function __construct(array $options)
    {
        $locale = $options['locale'] ?? null;
        /** @psalm-suppress DocblockTypeContradiction - Defensive check */
        if ($locale === null || $locale === '') {
            throw new InvalidArgumentException(
                'The locale must be provided in the `locale` options key as a non-empty-string',
            );
        }

        $this->type      = $options['type'] ?? NumberFormatter::TYPE_DOUBLE;
        $numberFormatter = NumberFormatter::create(
            $locale,
            $options['style'] ?? NumberFormatter::DEFAULT_STYLE,
        );
        assert($numberFormatter !== null);
        $this->formatter = $numberFormatter;
    }

    public function filter(mixed $value): mixed
    {
        if (! is_string($value) && ! $value instanceof Stringable) {
            return $value;
        }

        // phpcs:ignore WebimpressCodingStandard.NamingConventions
        $intlWarningHandler = static fn(int $errorNum, string $_): bool => $errorNum === E_WARNING;

        try {
            set_error_handler($intlWarningHandler);
            $result = $this->formatter->parse(
                (string) $value,
                $this->type,
            );
        } catch (IntlException) {
            return $value;
        } finally {
            restore_error_handler();
        }

        return $result === false ? $value : $result;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
