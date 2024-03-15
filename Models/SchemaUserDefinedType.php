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

class SchemaUserDefinedType extends ValueObject
{
    private bool $platformSupported = false;

    public function setSupport(bool $supported): self
    {
        $this->platformSupported = $supported;

        return $this;
    }

    public function isSupported(): bool
    {
        return $this->platformSupported || $this->getDataValue('supported');
    }

    public function getName()
    {
        return $this->getDataValue('UserDefinedTypeName');
    }

    public function getUserDefinedUserTypeId()
    {
        return $this->getDataValue('UserDefinedUserTypeId');
    }

    public function getSchemaName()
    {
        return $this->getDataValue('SchemaName');
    }

    public function getUserDefinedMaxLength()
    {
        return $this->getDataValue('UserDefinedMaxLength');
    }

    public function getUserDefinedPrecision()
    {
        return $this->getDataValue('UserDefinedPrecision');
    }

    public function getUserDefinedScale()
    {
        return $this->getDataValue('UserDefinedScale');
    }

    public function isNullable()
    {
        return $this->getDataValue('IsNullable');
    }

    public function getBaseSystemTypeName()
    {
        return $this->getDataValue('BaseSystemTypeName');
    }

    public function getBaseSystemTypeId()
    {
        return $this->getDataValue('BaseSystemTypeId');
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'supported' => $this->platformSupported,
            ]
        );
    }
}
