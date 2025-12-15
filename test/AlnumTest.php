<?php

declare(strict_types=1);

namespace LaminasTest\I18n\Filter;

use Laminas\Filter\Exception\InvalidArgumentException;
use Laminas\I18n\Filter\Alnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AlnumTest extends TestCase
{
    public function testAnEmptyLocaleInOptionsIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The locale must be provided in the `locale` options key as a non-empty-string');
        new Alnum([]);
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
        $stringable = new StringableObject('foo * bar');
        $object     = (object) ['foo' => 'bar'];

        return [
            // Allow whitespace false
            ['en', false, 'abc 123 !', 'abc123'],
            ['ja', false, 'aＡBｂ3４5６', 'aB35'],
            ['ja', false, 'z７ Ｙ8　x９', 'z8x'],
            ['ja', false, '，s1.2r３#:q,', 's12rq'],
            ['de', false, 'abc123', 'abc123'],
            ['de', false, 'abc 123', 'abc123'],
            ['de', false, 'abcxyz', 'abcxyz'],
            ['de', false, 'AZ@#4.3', 'AZ43'],
            ['en', false, 'abc123', 'abc123'],
            ['en', false, 'abc 123', 'abc123'],
            ['en', false, 'abcxyz', 'abcxyz'],
            ['en', false, 'če2t3ně', 'če2t3ně'],
            ['en', false, 'grz5e4gżółka', 'grz5e4gżółka'],
            ['en', false, 'Be3l5gië', 'Be3l5gië'],
            ['en', false, $stringable, 'foobar'],
            // Whitespace Only Input
            ['fr', false, '', ''],
            ['fr', false, "\n", ''],
            ['fr', false, " \t ", ''],
            ['fr', true, '', ''],
            ['fr', true, "\n", "\n"],
            ['fr', true, " \t ", " \t "],
            // Allow Whitespace true
            ['fr', true, 'abc123', 'abc123'],
            ['fr', true, 'abc 123', 'abc 123'],
            ['fr', true, 'abcxyz', 'abcxyz'],
            ['fr', true, 'AZ@#4.3', 'AZ43'],
            ['ko', true, 'a B ４5', 'a B 5'],
            ['ja', true, 'z3ッx', 'z3x'],
            ['en_US', true, 'abc123', 'abc123'],
            ['en_US', true, 'abc 123', 'abc 123'],
            ['en_US', true, 'abcxyz', 'abcxyz'],
            ['en_US', true, 'če2 t3ně', 'če2 t3ně'],
            ['en_US', true, 'gr z5e4gżółka', 'gr z5e4gżółka'],
            ['en_US', true, 'Be3l5 gië', 'Be3l5 gië'],
            ['en_US', true, '', ''],
            ['en', true, $stringable, 'foo  bar'],
            // Unfiltered Test Cases
            ['en', false, null, null],
            ['en', false, true, true],
            ['en', false, false, false],
            ['en', false, $object, $object],
            ['en', true, null, null],
            ['en', true, true, true],
            ['en', true, false, false],
            ['en', true, $object, $object],
            // Numbers are cast to string
            ['en', false, 123, '123'],
            ['en', true, 123, '123'],
            // Floats lose the decimal
            ['en', false, 123.45, '12345'],
            ['en', true, 123.45, '12345'],
            // Arrays are processed recursively
            ['en', false, ['%foo bar%', '^foo bar^'], ['foobar', 'foobar']],
            ['en', true, ['%foo bar%', '^foo bar^'], ['foo bar', 'foo bar']],
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
        $filter = new Alnum([
            'locale'            => $locale,
            'allow_white_space' => $allowWhitespace,
        ]);

        /** @psalm-var mixed $result */
        $result = $filter->filter($input);
        self::assertSame($result, $filter->__invoke($input));

        self::assertSame($expect, $result);
    }
}
