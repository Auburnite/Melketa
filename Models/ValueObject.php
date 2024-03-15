<?php

/*
 * This file is part of the Auburnite package.
 *
 * (c) Jordan Wamser <jwamser@redpandacoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Auburnite\Component\Malketa\Models;

class ValueObject implements \JsonSerializable
{
    final public function __construct(
        private array $data,
    ) {
    }

    protected function getDataValue(string $columnName)
    {
        return $this->data[$columnName] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
