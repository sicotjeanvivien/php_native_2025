<?php

namespace AWSD\Database\Schema\Enum;

/**
 * EntityType
 *
 * Represents the logical type of an entity property.
 * Used as an abstraction layer between PHP native types and SQL representations.
 */
enum EntityType: string
{
  // Basic PHP types
  case INT         = 'int';
  case FLOAT       = 'float';
  case STRING      = 'string';
  case BOOL        = 'bool';
  case DATETIME    = 'DateTime';
  case ARRAY       = 'array';
  case OBJECT      = 'object';
  case MIXED       = 'mixed';

    // Domain-specific or extended types
  case UUID        = 'uuid';
  case PRIMARY_INT = 'primaryInt';
  case TEXT        = 'text';

  /**
   * Resolves an EntityType from a native PHP type string.
   *
   * @param string|null $type The native PHP type (e.g. 'int', 'string', 'DateTime', etc.)
   * @return self The corresponding EntityType enum value.
   */
  public static function fromPhp(?string $type): self
  {
    return match ($type) {
      'int'      => self::INT,
      'float'    => self::FLOAT,
      'string'   => self::STRING,
      'bool'     => self::BOOL,
      'DateTime' => self::DATETIME,
      'array'    => self::ARRAY,
      'object'   => self::OBJECT,
      default    => self::MIXED,
    };
  }

  /**
   * Resolves an EntityType from a string passed via the #[Type] attribute.
   *
   * @param string $type The string value provided in the Type attribute.
   * @return self The corresponding EntityType value.
   * @throws \InvalidArgumentException If the string doesn't match any known type.
   */
  public static function fromAttribute(string $type): self
  {
    return self::tryFrom($type)
      ?? throw new \InvalidArgumentException("Unknown type: $type");
  }
}
