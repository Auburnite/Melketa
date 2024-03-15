<?php

/*
 * This file is part of the Auburnite package.
 *
 * (c) Jordan Wamser <jwamser@redpandacoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Auburnite\Component\Malketa;

use Auburnite\Component\Malketa\Models\SchemaUserDefinedType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Again this library is only for MSSQL, we will want to add some validation around this.
 */
class UserDefinedTypeManager
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @note Any types starting with "n" (nvarchar,nchar,etc) divide this by 2
     *
     * @throws Exception
     */
    public function fetchAliasTypes(): array
    {
        $sql = 'SELECT
    u.name AS UserDefinedTypeName,
    u.user_type_id AS UserDefinedUserTypeId,
    SCHEMA_NAME(u.schema_id) AS SchemaName,
    u.max_length AS UserDefinedMaxLength,
    u.precision AS UserDefinedPrecision,
    u.scale AS UserDefinedScale,
    u.is_nullable as IsNullable,
    s.name AS BaseSystemTypeName,
    s.system_type_id AS BaseSystemTypeId
FROM
    sys.types u
        INNER JOIN
    sys.types s ON u.system_type_id = s.system_type_id AND s.is_user_defined = 0 and s.system_type_id = s.user_type_id
WHERE
    u.is_user_defined = 1 and u.user_type_id > 256
ORDER BY
    SchemaName,
    UserDefinedTypeName';

        $result = $this->connection->executeQuery($sql);

        return $result->fetchAllAssociative();
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->connection->getDatabasePlatform();
    }

    /**
     * @note StringType, BinaryType: These types can specify length (max size of the data), fixed (whether the data size
     * is fixed), and nullable (whether the column can store NULL values).
     * @note DecimalType: This type uses precision (the number of significant digits) and scale (the number of digits after the decimal point), along with nullable.
     * @note SmallIntType, IntegerType, BigIntType, GuidType, BlobType, TextType, BooleanType: These types primarily consider nullable since their size or precision is not typically specified beyond the type itself in SQL Server.
     * @note FloatType: While precision is available, its interpretation differs between databases. For SQL Server, it represents the number of bits used to store the mantissa of the float and affects the precision of the number. It also includes nullable.
     * @note DateTimeType: Can specify precision for the fractional seconds precision and nullable.
     * @note \Doctrine\DBAL\Types\FloatType SQL Server treats 'precision' differently for floats.
     * @note \Doctrine\DBAL\Types\DateTimeType 'precision' for fractional seconds
     * @note \Doctrine\DBAL\Types\BlobType Typically large objects without a fixed size
     * @note \Doctrine\DBAL\Types\TextType For large text, SQL Server uses 'varchar(max)' or 'nvarchar(max)'
     */
    public static function getColumnOptionDefaultKeys(string $typeClass): array
    {
        $relevantOptionsMap = [
            \Doctrine\DBAL\Types\StringType::class => ['length', 'fixed', 'nullable'],
            \Doctrine\DBAL\Types\DecimalType::class => ['precision', 'scale', 'nullable'],
            \Doctrine\DBAL\Types\SmallIntType::class => ['nullable'],
            \Doctrine\DBAL\Types\IntegerType::class => ['nullable'],
            \Doctrine\DBAL\Types\FloatType::class => ['precision', 'nullable'],
            \Doctrine\DBAL\Types\GuidType::class => ['nullable'],
            \Doctrine\DBAL\Types\DateTimeType::class => ['precision', 'nullable'],
            \Doctrine\DBAL\Types\BinaryType::class => ['length', 'fixed', 'nullable'],
            \Doctrine\DBAL\Types\BigIntType::class => ['nullable'],
            \Doctrine\DBAL\Types\BlobType::class => ['nullable'],
            \Doctrine\DBAL\Types\TextType::class => ['nullable'],
            \Doctrine\DBAL\Types\BooleanType::class => ['nullable'],
        ];

        return $relevantOptionsMap[$typeClass] ?? [];
    }

    public static function createColumnOptions(SchemaUserDefinedType $typeObject): array
    {
        return [
            'length' => $typeObject->getUserDefinedMaxLength(),
            'fixed' => false, // This example assumes 'fixed' is not directly mapped; adjust as needed
            'precision' => $typeObject->getUserDefinedPrecision(),
            'scale' => $typeObject->getUserDefinedScale(),
            'nullable' => $typeObject->isNullable(),
        ];
    }
}
