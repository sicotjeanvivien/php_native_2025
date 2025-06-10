<?php

namespace AWSD\Schema\Attribute;

use AWSD\Schema\Enum\EntityType;

/**
 * Attribute #[Type]
 *
 * Used to annotate an entity property with additional metadata for SQL mapping.
 * Defines how a property should be mapped in terms of type, nullability, default value,
 * primary key status, and auto-increment behavior.
 *
 * @example
 * #[Type(type: EntityType::DATETIME, default: "CURRENT_TIMESTAMP")]
 * protected DateTime $createdAt;
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Type
{
  /**
   * @param EntityType|null $type
   *     Optional explicit type for SQL mapping. If null, PHP type will be inferred.
   *     Use EntityType enum values (e.g., EntityType::INT, EntityType::TEXT, etc.).
   *
   * @param bool $primary
   *     Marks the property as a PRIMARY KEY in the table definition.
   *
   * @param bool $autoincrement
   *     Enables AUTO_INCREMENT (MySQL) / SERIAL (PostgreSQL) / AUTOINCREMENT (SQLite).
   *     Only valid for integer-type primary keys.
   *
   * @param bool $nullable
   *     Allows NULL values for this column. Defaults to false.
   *
   * @param mixed $default
   *     Default value for the column. Can be scalar or string representing a SQL function (e.g., "CURRENT_TIMESTAMP").
   */
  public function __construct(
    public ?EntityType $type = null,
    public bool $primary = false,
    public bool $autoincrement = false,
    public bool $nullable = false,
    public mixed $default = null,
  ) {}
}
