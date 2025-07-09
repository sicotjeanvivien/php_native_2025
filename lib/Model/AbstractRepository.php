<?php

namespace AWSD\Model;

use AWSD\Database\QueryExecutor;
use AWSD\Database\Schema\Helper\StringHelper;

/**
 * Class AbstractRepository
 *
 * Base abstract class for all data repositories.
 * Provides shared functionality to interact with a database table using PDO,
 * including entity hydration, field validation, and data fetching logic.
 */
abstract class AbstractRepository
{

  /**
   * @var QueryExecutor Handles execution of SQL queries with proper binding and entity hydration.
   */
  protected QueryExecutor $queryExecutor;

  /**
   * @var string The name of the database table mapped to the entity.
   */
  protected readonly  string $table;

  /**
   * @var string The FQCN of the entity used for hydration.
   */
  protected readonly string $entityClass;

  /**
   * @var array<string> List of fields available in the entity, used for filtering and validation.
   */
  protected readonly array $fields;

  /**
   * AbstractRepository constructor.
   *
   * Initializes the Query Executor.
   */
  public function __construct()
  {
    if (StringHelper::isNotSnakeCase($this->table)) {
      throw new \InvalidArgumentException("Invalid table name: {$this->table}");
    }
    $this->queryExecutor = new QueryExecutor($this->entityClass);
    $this->fields = $this->getEntityFields();
  }

  /**
   * Finds a single entity by a given field and value.
   *
   * @param string $field The field name (must exist in the table).
   * @param mixed $value The value to search for.
   * @return mixed The entity instance if found, or null.
   */
  public function findOneBy(string $field, mixed $value): mixed
  {
    if ($this->isInvalidField($field)) {
      throw new \InvalidArgumentException("Field '{$field}' is not a valid property of entity {$this->entityClass}");
    }
    $query = "SELECT * FROM {$this->table} WHERE {$field} = :{$field} LIMIT 1";
    return $this->queryExecutor->executeQuery($query, [":{$field}" => $value]);
  }

  /**
   * Finds all entities.
   *
   * @return array An array of all entities.
   */
  public function findAll(): array
  {
    $query = "SELECT * FROM {$this->table}";
    return $this->queryExecutor->executeQuery($query);
  }

  /**
   * Uses reflection to retrieve declared property names of the entity class.
   *
   * @return array<string> A list of all properties declared in the entity.
   */
  protected function getEntityFields(): array
  {
    $reflection = new \ReflectionClass($this->entityClass);
    $properties = $reflection->getProperties();
    return array_map(fn($prop) => $prop->getName(), $properties);
  }

  /**
   * Checks whether the provided field name does not exist in the entity definition.
   *
   * @param string $field The name of the field to check.
   * @return bool True if the field is invalid, false otherwise.
   */
  protected function isInvalidField(string $field): bool
  {
    return !$this->hasField($field);
  }


  /**
   * Checks if the given field name is a valid property of the entity.
   *
   * @param string $field The field name to check.
   * @return bool True if the field exists, false otherwise.
   */
  protected function hasField(string $field): bool
  {
    return in_array($field, $this->fields);
  }
}
