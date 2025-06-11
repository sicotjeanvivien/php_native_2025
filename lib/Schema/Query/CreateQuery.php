<?php

namespace AWSD\Schema\Query;

use AWSD\Schema\Attribute\Type;
use AWSD\Schema\Mapper\Orchestrator\TypeMapper;
use ReflectionProperty;

/**
 * CreateQuery
 *
 * Generates a SQL CREATE TABLE statement for a given entity object.
 * Leverages AbstractQuery to resolve column definitions and table name automatically.
 */
class CreateQuery extends AbstractQuery implements QueryInterface
{

  public function __construct(object $entity)
  {
    parent::__construct($entity, [Type::class]);
  }

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

  /**
   * Builds a map of column names to their full SQL definitions (type + constraints).
   *
   * @return array<string, string> Column name → SQL definition
   */
  protected function getSqlColumns(): array
  {
    $columns = [];
    foreach ($this->getEntityProperties() as $prop) {
      $columns[$prop->getName()] = $this->getSqlColumnDefinition($prop);
    }
    return $columns;
  }


  /**
   * Generates the SQL definition string for a given property (type + constraints).
   *
   * @param ReflectionProperty $prop
   * @return string SQL column definition (e.g., "VARCHAR(255) NOT NULL")
   */
  protected function getSqlColumnDefinition(ReflectionProperty $prop): string
  {
    $metadata = $this->metadata[Type::class][$prop->getName()] ?? null;
    $typeMapper = new TypeMapper($prop, $metadata);
    return $typeMapper->getSqlType() . ' ' . $typeMapper->getSqlConstraints();
  }
}
