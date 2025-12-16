<?php

declare(strict_types=1);

namespace Laminas\I18n\Filter;

use Laminas\ServiceManager\ServiceManager;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
final readonly class ConfigProvider
{
    /**
     * Return general-purpose laminas-i18n-filter configuration.
     *
     * @return array{
     *     dependencies: ServiceManagerConfiguration,
     *     filters: ServiceManagerConfiguration,
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'filters'      => $this->getFilterConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return ServiceManagerConfiguration
     */
    private function getDependencyConfig(): array
    {
        return [];
    }

    /**
     * Return laminas-filter configuration.
     *
     * @return ServiceManagerConfiguration
     */
    public function getFilterConfig(): array
    {
        return [
            'factories' => [
                Alnum::class        => Factory\AlnumFactory::class,
                Alpha::class        => Factory\AlphaFactory::class,
                NumberFormat::class => Factory\NumberFormatFactory::class,
                NumberParse::class  => Factory\NumberParseFactory::class,
            ],
            'aliases'   => [
                'alnum'        => Alnum::class,
                'Alnum'        => Alnum::class,
                'alpha'        => Alpha::class,
                'Alpha'        => Alpha::class,
                'numberformat' => NumberFormat::class,
                'numberFormat' => NumberFormat::class,
                'NumberFormat' => NumberFormat::class,
                'numberparse'  => NumberParse::class,
                'numberParse'  => NumberParse::class,
                'NumberParse'  => NumberParse::class,
            ],
        ];
    }
}
