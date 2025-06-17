<?php

declare(strict_types=1);

namespace AWSD\Schema\Enum;

use AWSD\Schema\Enum\SqlDialect;
use InvalidArgumentException;

/**
 * Enum JoinType
 *
 * Represents all supported SQL JOIN types.
 * Can be resolved from a raw string (e.g., 'left join') and filtered per SQL dialect.
 */
enum JoinType: string
{
  case JOIN             = 'JOIN';
  case INNER_JOIN       = 'INNER JOIN';
  case LEFT_JOIN        = 'LEFT JOIN';
  case RIGHT_JOIN       = 'RIGHT JOIN';
  case FULL_OUTER_JOIN  = 'FULL OUTER JOIN';
  case CROSS_JOIN       = 'CROSS JOIN';

  /**
   * Parses a raw string into a JoinType enum case.
   *
   * @param string $value The raw JOIN type string (e.g. "left join")
   * @return JoinType The corresponding enum case
   * @throws InvalidArgumentException If the string doesn't match any known JOIN type
   */
  public static function fromString(string $value): JoinType
  {
    $normalized = strtoupper(trim($value));

    foreach (self::cases() as $type) {
      if ($type->value === $normalized) {
        return $type;
      }
    }

    throw new InvalidArgumentException("Unknown join type: '{$value}'.");
  }

  /**
   * Returns the list of JOIN types supported for a given SQL dialect.
   *
   * @param SqlDialect $dialect The SQL dialect (PostgreSQL, MySQL, SQLite)
   * @return self[] Array of supported JoinType cases
   */
  public static function supportedForDialect(SqlDialect $dialect): array
  {
    return match ($dialect) {
      SqlDialect::PGSQL => [
        self::JOIN,
        self::INNER_JOIN,
        self::LEFT_JOIN,
        self::RIGHT_JOIN,
        self::FULL_OUTER_JOIN,
        self::CROSS_JOIN
      ],
      SqlDialect::MYSQL => [
        self::JOIN,
        self::INNER_JOIN,
        self::LEFT_JOIN,
        self::RIGHT_JOIN,
        self::CROSS_JOIN
      ],
      SqlDialect::SQLITE => [
        self::JOIN,
        self::INNER_JOIN,
        self::LEFT_JOIN,
        self::CROSS_JOIN
      ],
    };
  }

  /**
   * Returns the list of JOIN types that are supported by all target SQL dialects.
   *
   * @return self[]
   */
  public static function generic(): array
  {
    return [
      self::JOIN,
      self::INNER_JOIN,
      self::LEFT_JOIN,
    ];
  }
}
