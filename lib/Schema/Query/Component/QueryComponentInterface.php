<?php

namespace AWSD\Schema\Query\Component;

/**
 * Interface QueryComponentInterface
 *
 * Represents a reusable and modular component of a SQL query (e.g., WHERE, JOIN, ORDER BY).
 * All components must implement a `build()` method that returns the SQL fragment
 * and any associated bound parameters.
 *
 * Contract:
 * - The `build()` method must return an associative array with:
 *   - 'sql'   => string : the SQL clause (may be empty if not applicable)
 *   - 'params'=> array  : key-value pairs for PDO binding
 *
 * Example return structure:
 * ```php
 * [
 *   'sql' => 'WHERE age > :age',
 *   'params' => [':age' => 18]
 * ]
 * ```
 */
interface QueryComponentInterface
{
  /**
   * Builds the SQL fragment for the component and returns it along with any bound parameters.
   *
   * @return array{sql: string, params: array<string, mixed>} The SQL fragment and its bind parameters.
   */
  public function build(): array;
}
