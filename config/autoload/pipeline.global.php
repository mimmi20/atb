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
use Mezzio\Helper\ServerUrlMiddleware;
use Middlewares\ClientIp;
use Middlewares\Lowercase;
use Middlewares\ReferrerSpam;
use Middlewares\ResponseTime;
use Middlewares\TrailingSlash;
use Mimmi20\Mezzio\Middleware\SetLocaleMiddleware;

return [
    'middleware_pipeline' => [
        [
            'middleware' => ErrorHandler::class,
        ],
        [
            'middleware' => ServerUrlMiddleware::class,
        ],
        [
            'middleware' => ResponseTime::class,
        ],
        [
            'middleware' => ReferrerSpam::class,
        ],
        [
            'middleware' => TrailingSlash::class,
        ],
        [
            'middleware' => Lowercase::class,
        ],
        [
            'middleware' => ClientIp::class,
        ],
        [
            'middleware' => SetLocaleMiddleware::class,
        ],
        [
            'middleware' => 'admin-pipeline',
            'path' => 'admin/',
            'host' => 'localhost',
        ],
        [
            'middleware' => 'default-pipeline',
            'path' => '/',
            'host' => 'localhost',
        ],
    ],
];
