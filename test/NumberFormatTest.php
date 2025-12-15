<?php

declare(strict_types=1);

namespace LaminasTest\I18n\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\I18n\Filter\NumberFormat;
use NumberFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function sprintf;

final class NumberFormatTest extends TestCase
{
    public function testAnEmptyLocaleInOptionsIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The locale must be provided in the `locale` options key as a non-empty-string');
        new NumberFormat([]);
    }

    /**
     * @return array<array-key, array{
     *     0: non-empty-string,
     *     1: int,
     *     2: NumberFormatter::TYPE_*,
     *     3: float|int|string,
     *     4: string,
     * }>
     */
    public static function numberToFormattedProvider(): array
    {
        return [
            [
                'en_US',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                1234567.8912346,
                '1,234,567.891',
            ],
            [
                'de_DE',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                1234567.8912346,
                '1.234.567,891',
            ],
            [
                'ru_RU',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                1234567.8912346,
                '1 234 567,891',
            ],
            [
                'ar@numbers=arab',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                1111.23,
                "\xD9\xA1٬\xD9\xA1\xD9\xA1\xD9\xA1٫\xD9\xA2\xD9\xA3",
            ],
            [
                'en_US',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                1234567.8912346,
                '1,234,567',
            ],
            [
                'de_DE',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                1234567.8912346,
                '1.234.567',
            ],
            [
                'ru_RU',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                1234567.8912346,
                '1 234 567',
            ],
            [
                'ar@numbers=arab',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_INT64,
                1111.23,
                "\xD9\xA1٬\xD9\xA1\xD9\xA1\xD9\xA1",
            ],
            [
                'en_US',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1234567.8912346',
                '1,234,567.891',
            ],
            [
                'de_DE',
                NumberFormatter::DEFAULT_STYLE,
                NumberFormatter::TYPE_DOUBLE,
                '1234567,8912346',
                '1.234.567,891',
            ],
        ];
    }

    /**
     * @param non-empty-string $locale
     * @param NumberFormatter::TYPE_* $type
     */
    #[DataProvider('numberToFormattedProvider')]
    public function testNumberToFormatted(
        string $locale,
        int $style,
        int $type,
        float|int|string $value,
        string $expected,
    ): void {
        $filter = new NumberFormat([
            'locale' => $locale,
            'style'  => $style,
            'type'   => $type,
        ]);

        $actual = $filter->filter($value);

        self::assertSame(
            $actual,
            $filter->__invoke($value),
        );

        self::assertSame(
            $expected,
            $actual,
            sprintf(
                'Expected the numeric value "%s" to be formatted to "%s" but "%s" was returned',
                (string) $value,
                $expected,
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
        $filter = new NumberFormat([
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
