<?php

declare(strict_types=1);

namespace Laminas\I18n\Filter;

use IntlException;
use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\FilterInterface;
use NumberFormatter;

use function assert;
use function is_float;
use function is_int;
use function is_string;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

/**
 * A filter that formats numeric values to localised numeric strings
 *
 * @psalm-type Options = array{
 *     locale?: non-empty-string,
 *     style?: int,
 *     type?: NumberFormatter::TYPE_*,
 * }
 * @implements FilterInterface<string>
 */
final readonly class NumberFormat implements FilterInterface
{
    private NumberFormatter $formatter;
    /** @var NumberFormatter::TYPE_* */
    private int $type;
    private NumberParse $parse;

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

        $this->parse = new NumberParse($options);
    }

    public function filter(mixed $value): mixed
    {
        /** @psalm-var mixed $parsed */
        $parsed = is_string($value)
            ? $this->parse->filter($value)
            : $value;

        if (! is_int($parsed) && ! is_float($parsed)) {
            return $value;
        }

        // phpcs:ignore WebimpressCodingStandard.NamingConventions
        $intlWarningHandler = static fn(int $errorNum, string $_): bool => $errorNum === E_WARNING;

        try {
            set_error_handler($intlWarningHandler);
            $result = $this->formatter->format(
                $parsed,
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
