<?php

/*
 * This file is part of the Auburnite package.
 *
 * (c) Jordan Wamser <jwamser@redpandacoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Auburnite\Auburnite\Component\Melketa;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

class TransactionalConnectionMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
    }
}
