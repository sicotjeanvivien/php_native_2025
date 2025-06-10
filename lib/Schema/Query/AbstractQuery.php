<?php

namespace AWSD\Schema\Query;

use ReflectionClass;
use ReflectionProperty;
use AWSD\Schema\Mapper\TypeMapper;

/**
 * AbstractQuery
 *
 * Base class for all SQL query generators (e.g., CreateQuery, InsertQuery).
 * Provides utilities to extract metadata from an entity object using reflection,
 * resolve the table name, and build column definitions for SQL generation.
 */
abstract class AbstractQuery implements QueryInterface
{
  /**
   * The entity object to generate a query for.
   *
   * @var object
   */
  protected object $entity;

  /**
   * ReflectionClass used to analyze the entity's structure.
   *
   * @var ReflectionClass
   */
  protected ReflectionClass $reflection;

  /**
   * Resolved table name for the entity.
   *
   * @var string
   */
  protected string $tableName;

  /**
   * @param object $entity The entity instance used as the source for query generation.
   */
  public function __construct(object $entity)
  {
    $this->entity = $entity;
    $this->reflection = new ReflectionClass($this->entity);
    $this->tableName = $this->resolveTableName();
  }

  /**
   * Infers the table name from the entity class.
   * Default implementation converts the class short name to lowercase and appends 's'.
   *
   * @return string
   */
  protected function resolveTableName(): string
  {
    $name = $this->reflection->getShortName();
    return strtolower($name) . 's';
  }

  /**
   * Returns all declared properties of the entity as ReflectionProperty instances.
   *
   * @return ReflectionProperty[]
   */
  protected function getEntityProperties(): array
  {
    return $this->reflection->getProperties();
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
    $typeMapper = new TypeMapper($prop);
    return $typeMapper->getSqlType() . ' ' . $typeMapper->getSqlConstraints();
  }
}
