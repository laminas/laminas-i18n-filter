<?php

declare(strict_types=1);

namespace LaminasTest\I18n\Filter;

use Laminas\Filter\ConfigProvider as FilterConfigProvider;
use Laminas\Filter\FilterPluginManager;
use Laminas\I18n\Filter\ConfigProvider;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_merge_recursive;
use function assert;
use function class_exists;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
final class FilterPluginManagerIntegrationTest extends TestCase
{
    private ServiceManager $container;

    protected function setUp(): void
    {
        $config = array_merge_recursive(
            (new FilterConfigProvider())->__invoke(),
            (new ConfigProvider())->__invoke(),
        );

        $dependencies = $config['dependencies'] ?? [];
        /** @psalm-suppress MixedAssignment */
        $dependencies['services'] ??= [];
        self::assertIsArray($dependencies['services']);
        $dependencies['services']['config'] = $config;

        /** @psalm-var ServiceManagerConfiguration $dependencies */
        $this->container = new ServiceManager($dependencies);
    }

    /** @return iterable<string, array{0: string, 1: class-string}> */
    public static function aliasProvider(): iterable
    {
        $aliases = (new ConfigProvider())->getFilterConfig()['aliases'] ?? [];

        foreach ($aliases as $alias => $class) {
            assert(class_exists($class));

            yield $alias => [$alias, $class];
        }
    }

    /** @param class-string $class */
    #[DataProvider('aliasProvider')]
    public function testFiltersCanBeBuiltByThePluginManager(string $alias, string $class): void
    {
        $plugins = $this->container->get(FilterPluginManager::class);
        self::assertInstanceOf(FilterPluginManager::class, $plugins);

        self::assertInstanceOf(
            $class,
            $plugins->get($alias),
        );
    }
}
