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

use App\Container\ApplicationConfigInjectionDelegator;
use App\Handler\AdminPageHandler;
use App\Handler\AdminPageHandlerFactory;
use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use App\Middleware\LowercaseFactory;
use App\Middleware\TrailingSlashFactory;
use App\Pipeline\AdminPipelineFactory;
use App\Pipeline\DefaultPipelineFactory;
use Laminas\Form\Factory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Mezzio\Application;
use Mezzio\Router\LaminasRouter;
use Mezzio\Router\Middleware\RouteMiddlewareFactory;
use Mezzio\Helper\UrlHelperFactory;
use Mezzio\Helper\UrlHelperMiddlewareFactory;
use Middlewares\Lowercase;
use Middlewares\TrailingSlash;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            // Fully\Qualified\ClassOrInterfaceName::class => Fully\Qualified\ClassName::class,
            'default-router' => LaminasRouter::class,
            'admin-router' => LaminasRouter::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            HomePageHandler::class => HomePageHandlerFactory::class,
            AdminPageHandler::class => AdminPageHandlerFactory::class,
            Factory::class => InvokableFactory::class,

            Lowercase::class => LowercaseFactory::class,
            TrailingSlash::class => TrailingSlashFactory::class,

            'admin-router-middleware'   => new RouteMiddlewareFactory('admin-router'),
            'admin-url-helper'          => new UrlHelperFactory('/admin', 'admin-router'),
            'admin-url-middleware'      => new UrlHelperMiddlewareFactory('admin-url-helper'),
            'admin-pipeline'            => AdminPipelineFactory::class,
            'admin-navigation'          => new \Mimmi20\Mezzio\Navigation\NavigationMiddlewareFactory(urlHelperServiceName: 'admin-url-helper'),

            'default-router-middleware' => new RouteMiddlewareFactory('default-router'),
            'default-url-helper'        => new UrlHelperFactory('/', 'default-router'),
            'default-url-middleware'    => new UrlHelperMiddlewareFactory('default-url-helper'),
            'default-pipeline'          => DefaultPipelineFactory::class,
            'default-navigation'        => new \Mimmi20\Mezzio\Navigation\NavigationMiddlewareFactory(urlHelperServiceName: 'default-url-helper'),
        ],

        'delegators' => [
            Application::class => [
                ApplicationConfigInjectionDelegator::class,
            ],
        ],
    ],
];
