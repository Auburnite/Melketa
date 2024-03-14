<?php

/*******************************************************************************
 * Auburnite
 *
 * @link                https://github.com/Auburnite/Auburnite
 * @copywrite           Copywrite (c) 2023-present | Jordan Wamser - RedPanda Coding
 * @license             https://github.com/Auburnite/Auburnite/blob/main/LICENSE
 ******************************************************************************/
namespace Auburnite\Component\Malketa\Models;

class ValueObject implements \JsonSerializable
{
    final public function __construct(
        private array $data
    ){}

    protected function getDataValue(string $columnName)
    {
        return $this->data[$columnName] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
