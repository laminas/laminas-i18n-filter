<?php

declare(strict_types=1);

namespace LaminasTest\I18n\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\I18n\Filter\Alpha;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AlphaTest extends TestCase
{
    public function testAnEmptyLocaleInOptionsIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The locale must be provided in the `locale` options key as a non-empty-string');
        new Alpha([]);
    }

    /**
     * @return list<array{
     *     0: non-empty-string,
     *     1: bool,
     *     2: mixed,
     *     3: mixed,
     * }>
     */
    public static function basicDataProvider(): array
    {
        $stringable = new StringableObject('foo 123 bar');
        $object     = (object) ['foo' => 'bar'];

        return [
            ['en_GB', false, 'abc123', 'abc'],
            ['en_GB', false, 'abc 123', 'abc'],
            ['en_GB', false, 'abcxyz', 'abcxyz'],
            ['en_GB', false, $stringable, 'foobar'],
            // multibyte alphabet
            ['ja', false, 'aＡBｂc', 'aBc'],
            // multibyte or singlebyte space
            ['ja', false, 'z Ｙ　x', 'zx'],
            // multibyte or singlebyte digits
            ['ja', false, 'Ｗ1v３Ｕ4t', 'vt'],
            // various multibyte or singlebyte characters
            ['ja', false, '，sй.rλ:qν＿p', 'srqp'],
            // singlebyte alphabets
            ['ja', false, 'onml', 'onml'],
            ['de', false, 'abc123', 'abc'],
            ['de', false, 'abc 123', 'abc'],
            ['de', false, 'abcxyz', 'abcxyz'],
            ['de', false, 'četně', 'četně'],
            ['de', false, 'لعربية', 'لعربية'],
            ['de', false, 'grzegżółka', 'grzegżółka'],
            ['de', false, 'België', 'België'],
            // Whitespace Only Input
            ['fr', false, '', ''],
            ['fr', false, "\n", ''],
            ['fr', false, " \t ", ''],
            ['fr', true, '', ''],
            ['fr', true, "\n", "\n"],
            ['fr', true, " \t ", " \t "],
            // Unfiltered Test Cases
            ['en', false, null, null],
            ['en', false, true, true],
            ['en', false, false, false],
            ['en', false, $object, $object],
            ['en', true, null, null],
            ['en', true, true, true],
            ['en', true, false, false],
            ['en', true, $object, $object],
            // Allow Whitespace
            ['en_GB', true, 'abc123', 'abc'],
            ['en_GB', true, 'abc 123', 'abc '],
            ['en_GB', true, 'a b cxyz', 'a b cxyz'],
            ['en_GB', true, $stringable, 'foo  bar'],
            ['ja', true, 'a B', 'a B'],
            ['ja', true, 'zＹッx', 'zx'],
        ];
    }

    /** @param non-empty-string $locale */
    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(
        string $locale,
        bool $allowWhitespace,
        mixed $input,
        mixed $expect,
    ): void {
        $filter = new Alpha([
            'locale'            => $locale,
            'allow_white_space' => $allowWhitespace,
        ]);

        /** @psalm-var mixed $result */
        $result = $filter->filter($input);
        self::assertSame($result, $filter->__invoke($input));

        self::assertSame($expect, $result);
    }

    public function testThatWhitespaceIsStrippedByDefault(): void
    {
        $filter = new Alpha([
            'locale' => 'en',
        ]);

        self::assertSame('abc', $filter->filter('!abc 123 !!'));
    }
}
