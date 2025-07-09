<?php

namespace AWSD\Database\Schema\Mapper\SGBD\Interface;

/**
 * SgbdMapperInterface
 *
 * Contract for all SQL dialect-specific mappers (e.g., MySQL, PostgreSQL, SQLite).
 * Each mapper is responsible for translating a field's logical type and metadata
 * into a SQL-compatible type and constraint string.
 */
interface TypeMapperInterface
{
  /**
   * Returns the SQL column type for the mapped field.
   *
   * @return string A valid SQL type string (e.g. VARCHAR(255), TEXT, INTEGER, etc.)
   */
  public function getType(): string;

  /**
   * Returns the full SQL constraint string for the mapped field.
   * May include PRIMARY KEY, AUTO_INCREMENT, NOT NULL, DEFAULT, etc.
   *
   * @return string SQL constraints to append after the type in a CREATE TABLE statement.
   */
  public function getConstraints(): string;
}
