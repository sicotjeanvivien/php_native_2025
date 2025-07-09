<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\SGBD\PostgreSQL;

use AWSD\Database\Schema\Mapper\SGBD\AbstractQuoteMapper;

final class QuoteMapper extends AbstractQuoteMapper
{
  public function __construct() {}

  /**
   * Quotes an identifier (table, column, alias) using PostgreSQL syntax.
   * Handles dot notation for qualified names (e.g., schema.table).
   */
  public function quoteIdentifier(string $identifier): string
  {
    // Split on dots for schema.table or table.column
    $parts = explode('.', $identifier);

    return implode('.', array_map(
      static fn(string $part): string => '"' . str_replace('"', '""', $part) . '"',
      $parts
    ));
  }

  /**
   * Quotes an alias clause using PostgreSQL rules.
   * Example: quoteAlias("username", "user_name") → "username" AS "user_name"
   */
  public function quoteAlias(string $identifier, string $alias): string
  {
    return $this->quoteIdentifier($identifier) . ' AS ' . $this->quoteIdentifier($alias);
  }

  /**
   * Quotes a SQL literal value (string, int, float, null, bool).
   * Strings are wrapped in single quotes with escaped single quotes.
   */
  public function quoteValue(mixed $value): string
  {
    return match (true) {
      is_null($value)   => 'NULL',
      is_bool($value)   => $value ? 'TRUE' : 'FALSE',
      is_int($value),
      is_float($value)  => (string) $value,
      is_string($value) => "'" . str_replace("'", "''", $value) . "'",
      default           => throw new \InvalidArgumentException('Unsupported value type for quoting: ' . gettype($value))
    };
  }
}
