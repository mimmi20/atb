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

namespace App\Handler;

use Laminas\Form\Factory;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;

final class HomePageHandlerFactory
{
    /** @throws ContainerExceptionInterface */
    public function __invoke(ContainerInterface $container): HomePageHandler
    {
        $renderer    = $container->get(TemplateRendererInterface::class);
        $formFactory = $container->get(Factory::class);
        $logger      = $container->get(LoggerInterface::class);

        assert($renderer instanceof TemplateRendererInterface);
        assert($formFactory instanceof Factory);
        assert($logger instanceof LoggerInterface);

        return new HomePageHandler($renderer, $formFactory, $logger);
    }
}
