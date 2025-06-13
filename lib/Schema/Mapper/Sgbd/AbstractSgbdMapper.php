<?php

namespace AWSD\Schema\Mapper\SGBD;


/**
 * AbstractSgbdMapper
 *
 * Base class for SQL dialect-specific mappers (PostgreSQL, MySQL, SQLite).
 * Handles type and constraint formatting for a given entity field.
 */
abstract class AbstractSGBDMapper
{
  /**
   * List of SQL functions that should not be quoted in DEFAULT clauses.
   *
   * @var string[]
   */
  private const SQL_FUNCTIONS_SUPPORT = [
    'ON UPDATE CURRENT_TIMESTAMP',
    'CURRENT_TIMESTAMP'
  ];

  /**
   * Formats a DEFAULT value for inclusion in a SQL column definition.
   * Ensures proper quoting unless the value is a recognized SQL function.
   *
   * @param mixed $value
   * @return string
   */
  protected function quoteDefault(mixed $value): string
  {
    if (is_string($value) && $this->isSqlFunction($value)) {
      return strtoupper(trim($value));
    }

    return match (gettype($value)) {
      'string' => "'" . addslashes($value) . "'",
      'bool'   => $value ? 'TRUE' : 'FALSE',
      default  => (string) $value,
    };
  }

  /**
   * Checks whether a string value is a recognized SQL function (e.g., CURRENT_TIMESTAMP).
   *
   * @param string $value
   * @return bool
   */
  private function isSqlFunction(string $value): bool
  {
    return in_array(strtoupper(trim($value)), self::SQL_FUNCTIONS_SUPPORT, true);
  }
}
