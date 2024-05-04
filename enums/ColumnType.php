<?php

namespace Entify\Enums;

enum ColumnType
{
    case INT;
    case VARCHAR;
    case TEXT;
    case DATE;
    case DATETIME;
    case TIMESTAMP;
    case TIME;
    case FLOAT;
    case DOUBLE;
    case DECIMAL;
    case BOOLEAN;
    case ENUM;
    case SET;
    case JSON;
    case JSONB;
    case BLOB;

    public static function fromYamlType(string $type): ColumnType
    {
        return match ($type) {
            'int' => self::INT,
            'varchar' => self::VARCHAR,
            'text' => self::TEXT,
            'date' => self::DATE,
            'datetime' => self::DATETIME,
            'timestamp' => self::TIMESTAMP,
            'time' => self::TIME,
            'float' => self::FLOAT,
            'double' => self::DOUBLE,
            'decimal' => self::DECIMAL,
            'boolean' => self::BOOLEAN,
            'enum' => self::ENUM,
            'set' => self::SET,
            'json' => self::JSON,
            'jsonb' => self::JSONB,
            'blob' => self::BLOB,
            default => throw new \Exception("Unknown type: $type"),
        };
    }

    public function toPhpType(): string
    {
        return match ($this) {
            self::INT => 'int',
            self::VARCHAR => 'string',
            self::TEXT => 'string',
            self::DATE => 'string',
            self::DATETIME => \DateTime::class,
            self::TIMESTAMP => \DateTime::class,
            self::TIME => 'string',
            self::FLOAT => 'float',
            self::DOUBLE => 'float',
            self::DECIMAL => 'float',
            self::BOOLEAN => 'bool',
            self::ENUM => 'string',
            self::SET => 'string',
            self::JSON => 'array',
            self::JSONB => 'array',
            self::BLOB => 'string',
        };
    }
}