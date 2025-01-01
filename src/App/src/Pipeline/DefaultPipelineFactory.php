<?php

/**
 * This file is part of the mimmi20/atb package.
 *
 * Copyright (c) 2024-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace App\Pipeline;

use App\Handler\HomePageHandler;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware as RouterMiddleware;
use Mezzio\Router\Middleware\RouteMiddlewareFactory;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouterInterface;
use Mimmi20\Mezzio\Navigation\NavigationMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

final class DefaultPipelineFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MiddlewarePipe
    {
        $factory = $container->get(MiddlewareFactory::class);
        assert($factory instanceof MiddlewareFactory);

        // First, create our middleware pipeline
        $pipeline = new MiddlewarePipe();
        // Register the routing middleware in the middleware pipeline.
        // This middleware registers the Mezzio\Router\RouteResult request attribute.
        // module-specific!
        $pipeline->pipe((new RouteMiddlewareFactory('default-router'))($container));
        $pipeline->pipe($factory->lazy(NavigationMiddleware::class));
        $pipeline->pipe($factory->lazy(RouterMiddleware\ImplicitHeadMiddleware::class));
        $pipeline->pipe($factory->lazy(RouterMiddleware\ImplicitOptionsMiddleware::class));
        $pipeline->pipe($factory->lazy(RouterMiddleware\MethodNotAllowedMiddleware::class));
        // module-specific!

        // Seed the UrlHelper with the routing results:
        $pipeline->pipe($factory->lazy('default-url-middleware'));
        $pipeline->pipe($factory->lazy(RouterMiddleware\DispatchMiddleware::class));
        $pipeline->pipe($factory->lazy(NotFoundHandler::class));

        // Second, we'll create our routes
        // Retrieve our module-specific router
        $router = $container->get('default-router');
        assert($router instanceof RouterInterface);

        // Create a route collector to simplify routing
        $routes = new RouteCollector($router);

        // Start routing:
        $route = $routes->get('/', $factory->lazy(HomePageHandler::class));
        $route->setOptions(['defaults' => ['id' => 'atb']]);

        // Return the pipeline now that we're done!
        return $pipeline;
    }
}
