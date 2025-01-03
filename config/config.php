<?php
/**
 * This file is part of the mimmi20/mezzio-sample-project package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = ['config_cache_path' => 'data/cache/config-cache.php'];

$aggregator = new ConfigAggregator(
    [
        \Mimmi20\Monolog\Formatter\ConfigProvider::class,
        \Mimmi20\MonologFactory\ConfigProvider::class,
        \Mimmi20\Mezzio\Middleware\ConfigProvider::class,
        \Laminas\Hydrator\ConfigProvider::class,
        \Laminas\Filter\ConfigProvider::class,
        \Mezzio\Authentication\ConfigProvider::class,
        \Mimmi20\Mezzio\GenericAuthorization\ConfigProvider::class,
        \Laminas\I18n\ConfigProvider::class,
        \Laminas\Form\ConfigProvider::class,
        \Laminas\InputFilter\ConfigProvider::class,
        \Mimmi20\Form\Links\ConfigProvider::class,
        \Mimmi20\Form\Element\Group\ConfigProvider::class,
        \Mimmi20\Form\Paragraph\ConfigProvider::class,
        \Mimmi20\Mezzio\Navigation\ConfigProvider::class,
        \Mezzio\LaminasView\ConfigProvider::class,
        \Mimmi20\LaminasView\Helper\PartialRenderer\ConfigProvider::class,
        \Mimmi20\LaminasView\Helper\HtmlElement\ConfigProvider::class,
        \Mimmi20\NavigationHelper\Htmlify\ConfigProvider::class,
        \Mimmi20\NavigationHelper\FindRoot\ConfigProvider::class,
        \Mimmi20\NavigationHelper\FindFromProperty\ConfigProvider::class,
        \Mimmi20\NavigationHelper\ConvertToPages\ConfigProvider::class,
        \Mimmi20\NavigationHelper\FindActive\ConfigProvider::class,
        \Mimmi20\NavigationHelper\Accept\ConfigProvider::class,
        \Mimmi20\NavigationHelper\ContainerParser\ConfigProvider::class,
        \Mimmi20\LaminasView\BootstrapForm\ConfigProvider::class,
        \Mimmi20\Mezzio\Navigation\LaminasView\ConfigProvider::class,
        \Mimmi20\Mezzio\Navigation\LaminasView\View\Helper\BootstrapNavigation\ConfigProvider::class,
        \Mimmi20\LaminasView\ViteUrl\ConfigProvider::class,
        \Laminas\HttpHandlerRunner\ConfigProvider::class,
        \Laminas\Validator\ConfigProvider::class,
        \Mezzio\Helper\ConfigProvider::class,
        \Mezzio\ConfigProvider::class,
        \Mezzio\Router\LaminasRouter\ConfigProvider::class,
        \Laminas\Router\ConfigProvider::class,
        \Mezzio\Router\ConfigProvider::class,
        \Mimmi20\Mezzio\Router\ConfigProvider::class,
        \Laminas\Diactoros\ConfigProvider::class,
        // Include cache configuration
        new ArrayProvider($cacheConfig),
        // Swoole config to overwrite some services (if installed)
        class_exists(\Mezzio\Swoole\ConfigProvider::class)
            ? \Mezzio\Swoole\ConfigProvider::class
            : static fn () => [],
        // Load application config in a pre-defined order in such a way that local settings
        // overwrite global settings. (Loaded as first to last):
        //   - `global.php`
        //   - `*.global.php`
        //   - `local.php`
        //   - `*.local.php`
        new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),
        // Load development config if it exists
        new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
    ],
    $cacheConfig['config_cache_path']
);

return $aggregator->getMergedConfig();
