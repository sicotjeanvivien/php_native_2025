<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\MySQL;

use AWSD\Schema\Mapper\SGBD\AbsctractQuoteMapper;

final class QuoteMapper extends AbsctractQuoteMapper
{
  public function __construct() {}

  /**
   * Quotes an identifier (table, column, alias) using MySQL syntax.
   * Handles dot notation for qualified names (e.g., db.table, table.column).
   */
  public function quoteIdentifier(string $identifier): string
  {
    // Split by dot to handle schema.table or table.column
    $parts = explode('.', $identifier);

    return implode('.', array_map(
      static fn(string $part): string => '`' . str_replace('`', '``', $part) . '`',
      $parts
    ));
  }

  /**
   * Quotes an alias clause using MySQL rules.
   * Example: quoteAlias("email", "user_email") → `email` AS `user_email`
   */
  public function quoteAlias(string $identifier, string $alias): string
  {
    return $this->quoteIdentifier($identifier) . ' AS ' . $this->quoteIdentifier($alias);
  }

  /**
   * Quotes a SQL literal value (string, int, float, null, bool).
   * MySQL accepts TRUE/FALSE, but treats them as 1/0.
   */
  public function quoteValue(mixed $value): string
  {
    return match (true) {
      is_null($value)   => 'NULL',
      is_bool($value)   => $value ? 'TRUE' : 'FALSE',
      is_int($value),
      is_float($value)  => (string) $value,
      is_string($value) => "'" . str_replace("'", "\\'", $value) . "'",
      default           => throw new \InvalidArgumentException('Unsupported value type for quoting: ' . gettype($value))
    };
  }
}
