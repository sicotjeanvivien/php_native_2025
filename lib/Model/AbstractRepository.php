<?php

namespace AWSD\Model;

use AWSD\Utils\Database;
use PDO;
use PDOStatement;

/**
 * Class AbstractRepository
 *
 * Abstract base class for repository classes. Provides common database operations
 * and serves as a foundation for specific repository implementations.
 */
abstract class AbstractRepository
{
  /**
   * @var PDO The database connection instance.
   */
  protected PDO $database;

  /**
   * @var string The name of the database table associated with the repository.
   */
  protected readonly  string $table;

  /**
   * @var string The fully qualified class name of the entity associated with the repository.
   */
  protected readonly string $entityClass;

  /**
   * @var array the fields possible to filter request sql
   */
  protected readonly array $fields;

  /**
   * AbstractRepository constructor.
   *
   * Initializes the database connection.
   */
  public function __construct()
  {
    $this->database = Database::getInstance();
    if (!preg_match('/^[a-z_]+$/i', $this->table)) {
      throw new \InvalidArgumentException("Invalid table name: {$this->table}");
    }
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
    if (!$this->hasField($field)) {
      throw new \InvalidArgumentException("Field '{$field}' is not a valid property of entity {$this->entityClass}");
    }
    $query = "SELECT * FROM {$this->table} WHERE {$field} = :{$field} LIMIT 1";
    return $this->executeQuery($query, [":{$field}" => $value]);
  }

  /**
   * Finds all entities.
   *
   * @return array An array of all entities.
   */
  public function findAll(): array
  {
    $query = "SELECT * FROM {$this->table}";
    return $this->executeQuery($query);
  }

  /**
   * Returns the list of public/protected property names of the entity class.
   *
   * Used to validate queryable fields against entity structure.
   *
   * @return array List of available property names.
   */
  protected function getEntityFields(): array
  {
    $reflection = new \ReflectionClass($this->entityClass);
    $properties = $reflection->getProperties();
    return array_map(fn($prop) => $prop->getName(), $properties);
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

  /**
   * Executes a SQL query with optional parameters.
   *
   * @param string $query The SQL query to execute.
   * @param array $params An associative array of query parameters.
   * @return mixed The query result, either a single entity, an array of entities, or null.
   */
  private function executeQuery(string $query, array $params = []): mixed
  {
    $stm = $this->prepareStatement($query, $params);
    if ($stm->execute()) {
      $stm->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);

      return match (true) {
        $stm->rowCount() === 1 => $stm->fetch(),
        $stm->rowCount() > 1   => $stm->fetchAll(),
        default                => null
      };
    }
    return null;
  }

  /**
   * Prepares a PDOStatement with bound parameters.
   *
   * @param string $query The SQL query.
   * @param array $params The parameters to bind.
   * @return \PDOStatement The prepared and bound statement.
   */
  private function prepareStatement(string $query, array $params = []): PDOStatement
  {
    $stm = $this->database->prepare($query);
    foreach ($params as $key => $param) {
      $param_type = gettype($param);
      $pdo_type = match (true) {
        $param_type === 'integer' => PDO::PARAM_INT,
        $param_type === 'boolean' => PDO::PARAM_BOOL,
        $param_type === 'null' => PDO::PARAM_NULL,
        $param_type === 'string' => PDO::PARAM_STR,
      };
      $stm->bindValue($key, $param, $pdo_type);
    }
    return $stm;
  }
}
