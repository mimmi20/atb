<?php
/**
 * This file is part of the mimmi20/atb package.
 *
 * Copyright (c) 2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use Laminas\Form\Factory;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class HomePageHandlerFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testFactory(): void
    {
        $renderer    = $this->createMock(TemplateRendererInterface::class);
        $formFactory = $this->createMock(Factory::class);
        $logger      = $this->createMock(LoggerInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $matcher   = self::exactly(3);
        $container
            ->expects($matcher)
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $renderer, $logger, $formFactory): mixed {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(TemplateRendererInterface::class, $id),
                        2 => self::assertSame(Factory::class, $id),
                        default => self::assertSame(LoggerInterface::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => $renderer,
                        2 => $formFactory,
                        default => $logger,
                    };
                },
            );

        $factory = new HomePageHandlerFactory();

        $homePage = $factory($container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
