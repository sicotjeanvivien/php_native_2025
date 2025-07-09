<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\SGBD\SQLite;

use AWSD\Database\Schema\Mapper\SGBD\AbstractQuoteMapper;

final class QuoteMapper extends AbstractQuoteMapper
{
  public function __construct() {}

  /**
   * Quotes an identifier using SQLite syntax.
   * SQLite accepte `"`, `[]`, ou `` ` `` pour les identifiants — ici on standardise avec `"`.
   */
  public function quoteIdentifier(string $identifier): string
  {
    $parts = explode('.', $identifier);

    return implode('.', array_map(
      static fn(string $part): string => '"' . str_replace('"', '""', $part) . '"',
      $parts
    ));
  }

  /**
   * Quotes an alias clause using SQLite rules.
   */
  public function quoteAlias(string $identifier, string $alias): string
  {
    return $this->quoteIdentifier($identifier) . ' AS ' . $this->quoteIdentifier($alias);
  }

  /**
   * Quotes a SQL literal value (string, int, float, null, bool).
   * SQLite n’a pas de type booléen → TRUE = 1, FALSE = 0.
   */
  public function quoteValue(mixed $value): string
  {
    return match (true) {
      is_null($value)   => 'NULL',
      is_bool($value)   => $value ? '1' : '0',
      is_int($value),
      is_float($value)  => (string) $value,
      is_string($value) => "'" . str_replace("'", "''", $value) . "'",
      default           => throw new \InvalidArgumentException('Unsupported value type for quoting: ' . gettype($value))
    };
  }
}
