<?php

declare(strict_types=1);

namespace LaminasTest\I18n\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\I18n\Filter\NumberParse;
use NumberFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;

final class NumberParseTest extends TestCase
{
    public function testAnEmptyLocaleInOptionsIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The locale must be provided in the `locale` options key as a non-empty-string');
        new NumberParse([]);
    }

    /**
     * @return list<array{
     *     0: non-empty-string,
     *     1: int,
     *     2: NumberFormatter::TYPE_*,
     *     3: string,
     *     4: float,
     * }>
     */
    public static function formattedToNumberProvider(): array
    {
        return [
            [
                'en_US',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1,234,567.891',
                1234567.891,
            ],
            [
                'de_DE',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1.234.567,891',
                1234567.891,
            ],
            [
                'de_DE',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1234567,891',
                1234567.891,
            ],
            [
                'ru_RU',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1 234 567,891',
                1234567.891,
            ],
            [
                'ru_RU',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1234567,891',
                1234567.891,
            ],
            [
                'ar@numbers=arab',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                "\xD9\xA1'\xD9\xA1\xD9\xA1\xD9\xA1,\xD9\xA2\xD9\xA3",
                1111.23,
            ],
            [
                'ar@numbers=arab',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                "\xD9\xA1\xD9\xA1\xD9\xA1\xD9\xA1,\xD9\xA2\xD9\xA3",
                1111.23,
            ],
            [
                'en_US',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                '1,234,567.891',
                1234567,
            ],
            [
                'de_DE',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                '1.234.567,891',
                1234567,
            ],
            [
                'ru_RU',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                '1 234 567,891',
                1234567,
            ],
            [
                'ar@numbers=arab',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                "\xD9\xA1'\xD9\xA1\xD9\xA1\xD9\xA1",
                1111,
            ],
        ];
    }

    /**
     * @param non-empty-string $locale
     * @param NumberFormatter::TYPE_* $type
     */
    #[DataProvider('formattedToNumberProvider')]
    public function testFormattedToNumber(
        string $locale,
        int $style,
        int $type,
        string $value,
        float|int $expected,
    ): void {
        $filter = new NumberParse([
            'locale' => $locale,
            'style'  => $style,
            'type'   => $type,
        ]);

        $actual = $filter->filter($value);

        self::assertSame(
            $actual,
            $filter->__invoke($value),
            'Invoke should yield the same result',
        );

        self::assertSame(
            $expected,
            $actual,
            sprintf(
                'The numeric string value "%s" was expected to be filtered to (%s) %0.2f but "%s" was returned',
                $value,
                gettype($expected),
                (float) $expected,
                (string) $actual,
            ),
        );
    }

    /** @return array<array-key, array{0: mixed}> */
    public static function formatNonNumberProvider(): array
    {
        return [
            [null],
            [[]],
            [(object) ['foo' => 1]],
            [false],
            [true],
            ['Miss Piggy'],
        ];
    }

    #[DataProvider('formatNonNumberProvider')]
    public function testFormattedWithNonNumbers(
        mixed $value,
    ): void {
        $filter = new NumberParse([
            'locale' => 'en_US',
            'style'  => NumberFormatter::DEFAULT_STYLE,
            'type'   => NumberFormatter::TYPE_DOUBLE,
        ]);

        self::assertEquals(
            $value,
            $filter->filter($value),
        );
    }
}
