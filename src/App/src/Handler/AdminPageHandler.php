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

use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\View\Model\ViewModel;
use Mezzio\Template\TemplateRendererInterface;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function assert;
use function is_string;

final readonly class AdminPageHandler implements RequestHandlerInterface
{
    /** @throws void */
    public function __construct(private TemplateRendererInterface $template, private LoggerInterface $logger)
    {
        // nothing to do
    }

    /** @throws InvalidArgumentException */
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');

        assert(is_string($id) || $id === null);

        $layout = new ViewModel(
            ['request' => $request],
        );
        $layout->setTemplate('layout::default');

        try {
            $html = $this->template->render(
                'app::admin-page',
                [
                    'layout' => $layout,
                    'request' => $request,
                ],
            );
        } catch (Throwable $e) {
            $this->logger->error($e);
            $html = '';
        }

        return (new HtmlResponse($html))->withHeader('X-Content-Type-Options', 'nosniff');
    }
}
