<?php

/*******************************************************************************
 * Auburnite
 *
 * @link                https://github.com/Auburnite/Auburnite
 * @copywrite           Copywrite (c) 2023-present | Jordan Wamser - RedPanda Coding
 * @license             https://github.com/Auburnite/Auburnite/blob/main/LICENSE
 ******************************************************************************/
namespace Auburnite\Auburnite\Component\Melketa;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

class TransactionalConnectionMiddleware implements Middleware
{

    public function wrap(Driver $driver): Driver
    {

    }
}
