<?php

declare(strict_types=1);

namespace LaminasTest\I18n\Filter;

use Stringable;

final readonly class StringableObject implements Stringable
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
