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

namespace App\Container;

use Closure;
use Mezzio\Application;
use Mezzio\Container\Exception\InvalidServiceException;
use Mezzio\Exception\InvalidArgumentException;
use Mezzio\Exception\InvalidMiddlewareException;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use SplPriorityQueue;

use function array_key_exists;
use function array_map;
use function array_reduce;
use function assert;
use function get_debug_type;
use function gettype;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function Laminas\Stratigility\host;
use function Laminas\Stratigility\path;
use function sprintf;

use const PHP_INT_MAX;

final class ApplicationConfigInjectionDelegator
{
    /**
     * Decorate an Application instance by injecting routes and/or middleware
     * from configuration.
     *
     * @throws InvalidServiceException if the $callback produces
     *     something other than an `Application` instance, as the delegator cannot
     *     proceed with its operations
     * @throws InvalidMiddlewareException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $application = $callback();

        if (!$application instanceof Application) {
            throw new InvalidServiceException(sprintf(
                'Delegator factory %s cannot operate on a %s; please map it only to the %s service',
                self::class,
                is_object($application) ? $application::class . ' instance' : gettype($application),
                Application::class,
            ));
        }

        if (!$container->has('config')) {
            return $application;
        }

        /**
         * The array shape is forced here as it cannot be inferred
         * @var array{middleware_pipeline?: list<mixed>, routes?: array<int|string, array{path?: non-empty-string, middleware?:array<non-empty-string>|non-empty-string, allowed_methods?: list<string>|null, options?: array<string, mixed>}>} $config
         */
        $config = $container->get('config');

        $config['middleware_pipeline'] ??= [];
        $config['routes']              ??= [];

        if ($config['middleware_pipeline'] !== []) {
            $this->injectPipelineFromConfig($container, $application, (array) $config);
        }

        if ($config['routes'] !== []) {
            $this->injectRoutesFromConfig($application, (array) $config);
        }

        return $application;
    }

    /**
     * Inject a middleware pipeline from the middleware_pipeline configuration.
     *
     * Inspects the configuration provided to determine if a middleware pipeline
     * exists to inject in the application.
     *
     * Use the following configuration format:
     *
     * <code>
     * return [
     *     'middleware_pipeline' => [
     *         // An array of middleware to register with the pipeline.
     *         // entries to register prior to routing/dispatching...
     *         // - entry for \Mezzio\Router\Middleware\PathBasedRoutingMiddleware::class
     *         // - entry for \Mezzio\Router\Middleware\MethodNotAllowedMiddleware::class
     *         // - entry for \Mezzio\Router\Middleware\DispatchMiddleware::class
     *         // entries to register after routing/dispatching...
     *     ],
     * ];
     * </code>
     *
     * Each item in the middleware_pipeline array must be of the following
     * specification:
     *
     * <code>
     * [
     *     // required:
     *     'middleware' => 'Name of middleware service, or a callable',
     *     // optional:
     *     'path'  => '/path/to/match',
     *     'priority' => 1, // integer
     * ]
     * </code>
     *
     * Note that the `path` element can only be a literal.
     *
     * `priority` is used to shape the order in which middleware is piped to the
     * application. Values are integers, with high values having higher priority
     * (piped earlier), and low/negative values having lower priority (piped last).
     * Default priority if none is specified is 1. Middleware with the same
     * priority are piped in the order in which they appear.
     *
     * Middleware piped may be either callables or service names.
     *
     * Additionally, you can specify an array of callables or service names as
     * the `middleware` value of a specification. Internally, this will create
     * a `Laminas\Stratigility\MiddlewarePipe` instance, with the middleware
     * specified piped in the order provided.
     *
     * @param array{middleware_pipeline?: list<mixed>} $config
     *
     * @throws InvalidMiddlewareException
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     */
    private function injectPipelineFromConfig(
        ContainerInterface $container,
        Application $application,
        array $config,
    ): void {
        $middlewarePipeline = $config['middleware_pipeline'] ?? [];

        if ($middlewarePipeline === []) {
            return;
        }

        $factory = $container->get(MiddlewareFactory::class);
        assert($factory instanceof MiddlewareFactory);

        /**
         * Create a priority queue from the specifications
         *
         * @var SplPriorityQueue<int, array{middleware: list<non-empty-string>, path?: string, host?: string}> $queue
         */
        $queue = array_reduce(
            array_map(self::createCollectionMapper(), $middlewarePipeline),
            self::createPriorityQueueReducer(),
            new SplPriorityQueue(),
        );

        foreach ($queue as $spec) {
            assert(is_array($spec));
            $pipe = $factory->prepare($spec['middleware']);

            if (array_key_exists('path', $spec)) {
                $pipe = path($spec['path'], $pipe);
            }

            if (array_key_exists('host', $spec)) {
                $pipe = host($spec['host'], $pipe);
            }

            $application->pipe($pipe);
        }
    }

    /**
     * Inject routes from configuration.
     *
     * Introspects the provided configuration for routes to inject in the
     * application instance.
     *
     * The following configuration structure can be used to define routes:
     *
     * <code>
     * return [
     *     'routes' => [
     *         [
     *             'path' => '/path/to/match',
     *             'middleware' => 'Middleware Service Name or Callable',
     *             'allowed_methods' => ['GET', 'POST', 'PATCH'],
     *             'options' => [
     *                 'stuff' => 'to',
     *                 'pass'  => 'to',
     *                 'the'   => 'underlying router',
     *             ],
     *         ],
     *         // etc.
     *     ],
     * ];
     * </code>
     *
     * Each route MUST have a path and middleware key at the minimum.
     *
     * The "allowed_methods" key may be omitted, can be either an array or the
     * value of the Mezzio\Router\Route::HTTP_METHOD_ANY constant; any
     * valid HTTP method token is allowed, which means you can specify custom HTTP
     * methods as well.
     *
     * The "options" key may also be omitted, and its interpretation will be
     * dependent on the underlying router used.
     *
     * @phpstan-param array{routes?: array<int|string, array{path?: non-empty-string, middleware?:non-empty-string|array<non-empty-string>, allowed_methods?: list<string>|null, options?: array<string, mixed>}>} $config
     *
     * @throws InvalidArgumentException
     */
    private function injectRoutesFromConfig(Application $application, array $config): void
    {
        $routes = $config['routes'] ?? [];

        if (!is_array($routes) || $routes === []) {
            return;
        }

        foreach ($routes as $key => $spec) {
            if (!isset($spec['path']) || !isset($spec['middleware'])) {
                continue;
            }

            $methods = Route::HTTP_METHOD_ANY;

            if (isset($spec['allowed_methods'])) {
                $methods = $spec['allowed_methods'];

                if (!is_array($methods)) {
                    throw new InvalidArgumentException(sprintf(
                        'Allowed HTTP methods for a route must be in form of an array; received "%s"',
                        gettype($methods),
                    ));
                }
            }

            $name = $spec['name'] ?? (is_string($key) ? $key : null);
            assert((is_string($name) && $name !== '') || $name === null);
            $route = $application->route($spec['path'], $spec['middleware'], $methods, $name);

            if (!isset($spec['options'])) {
                continue;
            }

            $options = $spec['options'];

            if (!is_array($options)) {
                throw new InvalidArgumentException(sprintf(
                    'Route options must be an array; received "%s"',
                    gettype($options),
                ));
            }

            $route->setOptions($options);
        }
    }

    /**
     * Create the collection mapping function.
     *
     * Returns a callable with the following signature:
     *
     * <code>
     * function (array|string $item) : array
     * </code>
     *
     * If the 'middleware' value is missing, or not viable as middleware, it
     * raises an exception, to ensure the pipeline is built correctly.
     *
     * @return Closure(mixed): non-empty-array<mixed>
     *
     * @throws InvalidArgumentException
     */
    private static function createCollectionMapper(): Closure
    {
        return static function ($item): array {
            if (!is_array($item) || !array_key_exists('middleware', $item)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid pipeline specification received; must be an array'
                    . ' containing a middleware key; received %s',
                    get_debug_type($item),
                ));
            }

            return $item;
        };
    }

    /**
     * Create reducer function that will reduce an array to a priority queue.
     *
     * Creates and returns a function with the signature:
     *
     * <code>
     * function (SplQueue $queue, array $item) : SplQueue
     * </code>
     *
     * The function is useful to reduce an array of pipeline middleware to a
     * priority queue.
     *
     * @return Closure(SplPriorityQueue<int, mixed>, array<string, mixed>): SplPriorityQueue<int, mixed>
     *
     * @throws void
     */
    private static function createPriorityQueueReducer(): Closure
    {
        // $serial is used to ensure that items of the same priority are enqueued
        // in the order in which they are inserted.
        $serial = PHP_INT_MAX;

        /*
         * @param SplPriorityQueue<int, mixed> $queue
         * @param array<string, mixed> $item
         *
         * @return Closure(SplPriorityQueue<int, mixed>, array<string, mixed>): SplPriorityQueue<int, mixed>
         *
         * @throws void
         */
        return static function (SplPriorityQueue $queue, array $item) use (&$serial): SplPriorityQueue {
            $priority = isset($item['priority']) && is_int($item['priority'])
                ? $item['priority']
                : 1;

            $queue->insert($item, [$priority, $serial]);
            --$serial;

            return $queue;
        };
    }
}
