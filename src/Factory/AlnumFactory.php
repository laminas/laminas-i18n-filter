<?php

declare(strict_types=1);

namespace Laminas\I18n\Filter\Factory;

use Laminas\I18n\Filter\Alnum;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function array_merge;

/**
 * This class is internal and as such is not subject to any backwards compatibility guarantees.
 *
 * @internal
 *
 * @psalm-internal \Laminas\I18n\Filter
 * @psalm-internal \LaminasTest\I18n\Filter
 *
 * @psalm-import-type Options from Alnum
 */
final readonly class AlnumFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array|null $options = null,
    ): Alnum {
        /** @var Options $options */
        $options = array_merge([
            'locale' => DetermineDefaultLocale::fromConfigWithSystemFallback($container),
        ], $options ?? []);

        return new Alnum($options);
    }
}
