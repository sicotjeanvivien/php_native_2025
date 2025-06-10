<?php

namespace AWSD\Schema\Query;

/**
 * CreateQuery
 *
 * Generates a SQL CREATE TABLE statement for a given entity object.
 * Leverages AbstractQuery to resolve column definitions and table name automatically.
 */
class CreateQuery extends AbstractQuery
{
  /**
   * Generates the full SQL CREATE TABLE statement for the entity.
   * Includes type, constraints, and column names, based on metadata and property analysis.
   *
   * @return string The SQL statement (e.g., CREATE TABLE IF NOT EXISTS ...).
   */
  public function generateSql(): string
  {
    $columns = $this->getSqlColumns();

    $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->tableName . " (\n";
    $lines = [];

    foreach ($columns as $name => $definition) {
      $lines[] = "  $name $definition";
    }

    $sql .= implode(",\n", $lines) . "\n);";

    return $sql;
  }
}
