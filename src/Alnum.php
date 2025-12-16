<?php

declare(strict_types=1);

namespace Laminas\I18n\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\Filter\FilterInterface;
use Laminas\I18n\Filter\Internal\ScalarOrArrayFilterCallback;
use Locale;

use function assert;
use function in_array;
use function is_string;
use function preg_replace;

/**
 * Filters a string argument, removing all non-alphanumeric characters
 *
 * @psalm-type Options = array{
 *     locale?: non-empty-string,
 *     allow_white_space?: bool,
 * }
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final readonly class Alnum implements FilterInterface
{
    private bool $allowWhiteSpace;

    /** @var non-empty-string */
    private string $locale;

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

        $this->locale          = $locale;
        $this->allowWhiteSpace = $options['allow_white_space'] ?? false;
    }

    public function filter(mixed $value): mixed
    {
        $whiteSpace = $this->allowWhiteSpace ? '\s' : '';
        $language   = Locale::getPrimaryLanguage($this->locale);

        if (in_array($language, ['ja', 'ko', 'zh'], true)) {
            // Use english alphabet
            $pattern = '/[^a-zA-Z0-9' . $whiteSpace . ']/u';
        } else {
            // Use native language alphabet
            $pattern = '/[^\p{L}\p{N}' . $whiteSpace . ']/u';
        }

        return ScalarOrArrayFilterCallback::applyRecursively(
            $value,
            static function (string $input) use ($pattern): string {
                $result = preg_replace($pattern, '', $input);
                assert(is_string($result));

                return $result;
            },
        );
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
