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

$year = date('Y');

$header = <<<EOF
    This file is part of the mimmi20/atb package.

    Copyright (c) 2024-{$year}, Thomas Mueller <mimmi20@live.de>

    For the full copyright and license information, please view the LICENSE
    file that was distributed with this source code.
    EOF;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->name('*.stub')
    ->notName('TemplatePath.stub')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/stubs')
    ->in(__DIR__ . '/tests')
    ->append([__DIR__ . '/rector.php'])
    ->append([__DIR__ . '/composer-dependency-analyser.php'])
    ->append([__FILE__]);

$rules = require 'vendor/mimmi20/coding-standard/src/php-cs-fixer.config.php';

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules(
        array_merge(
            $rules,
            [
                'header_comment' => [
                    'header' => $header,
                    'comment_type' => 'PHPDoc',
                    'location' => 'after_open',
                    'separate' => 'bottom',
                ],
            ],
        ),
    )
    ->setUsingCache(false)
    ->setFinder($finder);
