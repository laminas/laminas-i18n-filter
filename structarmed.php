<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Internal', 'src/Internal')
    ->layer('Factory', 'src/Factory')
    ->layer('ConfigProvider', 'src/ConfigProvider.php')
    ->layerPattern(
        'Filter',
        '/^Laminas\\\\I18n\\\\Filter\\\\.*$/',
        [
            '/^Laminas\\\\I18n\\\\Filter\\\\Factory\\\\.*$/',
            '/^Laminas\\\\I18n\\\\Filter\\\\Internal\\\\.*$/',
            '/^Laminas\\\\I18n\\\\Filter\\\\ConfigProvider$/',
        ]
    )
    ->ruleset([
        'Internal'       => [],
        'Filter'         => ['Internal'],
        'Factory'        => ['+Filter'],
        'ConfigProvider' => ['+Factory'],
    ]);
