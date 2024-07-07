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

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddlewareFactory;
use Mezzio\Router\RouterInterface;
use Middlewares\ResponseTime;
use Mimmi20\Mezzio\Middleware\SetLocaleMiddleware;
use Mimmi20\Mezzio\Navigation\NavigationMiddleware;
use Psr\Container\ContainerInterface;
use function Laminas\Stratigility\host;
use function Laminas\Stratigility\path;

/**
 * Setup middleware pipeline
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
 */
return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // The error handler should be the first (most outer) middleware to catch
    // all Exceptions.
    $app->pipe(ErrorHandler::class);
    $app->pipe(ServerUrlMiddleware::class);

    // Pipe more middleware here that you want to execute on every request:
    // - bootstrapping
    // - pre-conditions
    // - modifications to outgoing responses
    //
    // Piped Middleware may be either callables or service names. Middleware may
    // also be passed as an array; each item in the array must resolve to
    // middleware eventually (i.e., callable or service name).
    //
    // Middleware can be attached to specific paths, allowing you to mix and match
    // applications under a common domain.  The handlers in each middleware
    // attached this way will see a URI with the matched path segment removed.
    //
    // i.e., path of "/api/member/profile" only passes "/member/profile" to $apiMiddleware
    // - $app->pipe('/api', $apiMiddleware);
    // - $app->pipe('/docs', $apiDocMiddleware);
    // - $app->pipe('/files', $filesMiddleware);

    $app->pipe(SetLocaleMiddleware::class);
    $app->pipe(ResponseTime::class);

    $app->pipe(path('/admin', $factory->lazy('admin-pipeline')));
    $app->pipe(path('/', $factory->lazy('default-pipeline')));
    $app->pipe(host('admin.example.org', path('/admin', $factory->lazy('admin-pipeline'))));
};
