<?php

/*******************************************************************************
 * Auburnite
 *
 * @link                https://github.com/Auburnite/Auburnite
 * @copywrite           Copywrite (c) 2023-present | Jordan Wamser - RedPanda Coding
 * @license             https://github.com/Auburnite/Auburnite/blob/main/LICENSE
 ******************************************************************************/
namespace Auburnite\Component\Malketa\Generator;

use Auburnite\Component\Malketa\Models\SchemaUserDefinedType;
use Auburnite\Component\Malketa\UserDefinedTypeManager;

class ColumnOptionConfigGenerator implements \Stringable
{
    public function __construct(
        private SchemaUserDefinedType $typeObject,
        private string $doctrineBaseType
    ) {
    }

    public function __toString(): string
    {
        $optionsToBeUsed = UserDefinedTypeManager::createColumnOptions($this->typeObject);

        $toUse = UserDefinedTypeManager::getColumnOptionDefaultKeys(
            $this->doctrineBaseType);
        foreach ($optionsToBeUsed as $option => $value) {
            if (!in_array($option,$toUse
            )) {
                unset($optionsToBeUsed[$option]);
            }
        }

        $parts = [];
        foreach ($optionsToBeUsed as $key => $value) {
            // Format the value as a string or boolean as needed
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $parts[] = sprintf("'%s' => %s", $key, $formattedValue);
        }
        return sprintf("[\n            %s\n        ]", implode(",\n            ", $parts));
    }
}
