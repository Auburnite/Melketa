<?php

/*******************************************************************************
 * Auburnite
 *
 * @link                https://github.com/Auburnite/Auburnite
 * @copywrite           Copywrite (c) 2023-present | Jordan Wamser - RedPanda Coding
 * @license             https://github.com/Auburnite/Auburnite/blob/main/LICENSE
 ******************************************************************************/
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
