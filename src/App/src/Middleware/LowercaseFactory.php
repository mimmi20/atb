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

namespace App\Middleware;

use App\Handler\HomePageHandler;
use Laminas\Form\Factory;
use Mezzio\Template\TemplateRendererInterface;
use Middlewares\Lowercase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;

final class LowercaseFactory
{
    /** @throws ContainerExceptionInterface */
    public function __invoke(ContainerInterface $container): Lowercase
    {
        $middleware = new Lowercase();
        $middleware->redirect();

        return $middleware;
    }
}
