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

/*
 * aufgrund des ACL-Aufbaus sollte die Ressource dem Controller und das Privileg der Action entsprechen
 * Menüs sollten kein sinnvolles Linkziel haben, da diese ohnehin nicht anklickbar sind
 */

return [
    'navigation' => [
        'top' => [
        ],
        'bottom' => [
        ],
    ],
];
