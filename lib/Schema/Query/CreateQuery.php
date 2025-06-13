<?php

namespace AWSD\Schema\Query;

use AWSD\Schema\Attribute\Type;
use AWSD\Schema\Helper\StringHelper;
use AWSD\Schema\Mapper\Orchestrator\TypeMapper;
use ReflectionProperty;

/**
 * Class CreateQuery
 *
 * Generates a SQL CREATE TABLE statement for a given entity.
 *
 * This query builder leverages PHP attributes (#[Type]) and reflection to:
 * - Inspect all typed properties of an entity
 * - Map them to the correct SQL type and constraints (via TypeMapper)
 * - Generate a full CREATE TABLE IF NOT EXISTS statement
 *
 * Responsibilities:
 * - Validate that all property names follow snake_case conventions
 * - Build column definitions using attached #[Type] metadata
 * - Delegate SQL type/constraint resolution to TypeMapper
 *
 * Example usage:
 *   $query = new CreateQuery($userEntity);
 *   $sql = $query->generateSql();
 *
 * Throws:
 * - InvalidArgumentException if a property name is not in snake_case
 */

class CreateQuery extends AbstractQuery implements QueryInterface
{

  /**
   * Constructor
   *
   * Initializes the CreateQuery with the given entity instance.
   *
   * @param object $entity The entity instance (e.g. new User()).
   */
  public function __construct(object $entity)
  {
    parent::__construct($entity, [Type::class]);
  }

  /**
   * Generates the full SQL CREATE TABLE statement for the entity.
   *
   * Validates snake_case naming for each field.
   * Uses metadata from #[Type] attributes to resolve types and constraints.
   *
   * @return string The SQL CREATE TABLE statement.
   * @throws \InvalidArgumentException If a field name is not in snake_case.
   */
  public function generateSql(): string
  {
    $columns = $this->getSqlColumns();

    $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->tableName . " (\n";
    $lines = [];

    foreach ($columns as $name => $definition) {
      if (StringHelper::isNotSnakeCase($name)) {
        throw new \InvalidArgumentException("the $name is not a snake case");
      }
      $nameField = $name;
      $lines[] = "  $nameField $definition";
    }

    $sql .= implode(",\n", $lines) . "\n);";

    return $sql;
  }

  /**
   * Builds an associative array of column names to SQL definitions.
   *
   * Uses reflection and TypeMapper to resolve each field's full SQL column definition.
   *
   * @return array<string, string> Array mapping field names to SQL definitions.
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
   * Builds the SQL definition string for a specific property.
   *
   * Combines the SQL type and constraints based on the #[Type] attribute.
   *
   * @param ReflectionProperty $prop The reflected property of the entity.
   * @return string The complete SQL column definition.
   */
  protected function getSqlColumnDefinition(ReflectionProperty $prop): string
  {
    $metadata = $this->metadata[Type::class][$prop->getName()] ?? null;
    $typeMapper = new TypeMapper($prop, $metadata);
    return $typeMapper->getSqlType() . ' ' . $typeMapper->getSqlConstraints();
  }
}
