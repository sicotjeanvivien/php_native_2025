<?php

declare(strict_types=1);

namespace AWSD\Database;

use PDO;
use PDOStatement;

/**
 * Class QueryExecutor
 *
 * Provides safe and flexible execution of SQL queries using PDO,
 * with full support for parameter binding and typed entity hydration.
 *
 * Responsibilities:
 * - Centralize PDO query execution
 * - Handle INSERT/UPDATE/DELETE via `executeNonQuery()`
 * - Handle SELECT with optional entity hydration via `executeQuery()` and `fetchAllEntities()`
 * - Perform automatic type casting for hydrated properties (e.g. DateTime, int)
 *
 * Typical usage:
 * ```php
 * $executor = new QueryExecutor(User::class);
 * $user = $executor->executeQuery('SELECT * FROM users WHERE id = :id', [':id' => 1]);
 * ```
 */
class QueryExecutor
{
  /**
   * @var PDO The PDO connection to the current database.
   */
  protected PDO $database;

  /**
   * @var string|null Fully-qualified class name of the associated entity, or null.
   */
  protected readonly ?string $entityClass;

  /**
   * Constructor
   *
   * @param string|null $entityClass Optional entity class name for hydration.
   */
  public function __construct(?string $entityClass)
  {
    $this->database = Database::getInstance();
    $this->entityClass = $entityClass;
  }

  /**
   * Executes a non-SELECT SQL query (INSERT, UPDATE, DELETE).
   *
   * @param string $query  The SQL statement to execute.
   * @param array  $params The parameters to bind.
   * @return bool True on success, false on failure.
   */
  public function executeNonQuery(string $query, array $params = []): bool
  {
    $stm = $this->prepareStatement($query, $params);
    return $stm->execute();
  }

  /**
   * Executes a SELECT query and hydrates results as entities (if configured).
   *
   * @param string $query  The SQL SELECT query.
   * @param array  $params The parameters to bind.
   * @return object|array<object>|null A single entity, a list of entities, or null.
   */
  public function executeQuery(string $query, array $params = []): mixed
  {
    $stm = $this->prepareStatement($query, $params);
    $stm->setFetchMode(PDO::FETCH_ASSOC);

    return $stm->execute()
      ? match (true) {
        $stm->rowCount() === 1 => $this->hydrateWithCasting($stm->fetch()),
        $stm->rowCount() > 1   => array_map([$this, 'hydrateWithCasting'], $stm->fetchAll()),
        default                => null
      }
      : null;
  }

  /**
   * Executes raw SQL (DDL, etc.) without parameters.
   *
   * @param string $sql Raw SQL statement.
   * @return bool True on success, false otherwise.
   */
  public function executeRaw(string $sql): bool
  {
    return $this->database->exec($sql) !== false;
  }

  /**
   * Returns the ID of the last inserted row.
   *
   * @return string The last insert ID.
   */
  public function lastInsertId(): string
  {
    return $this->database->lastInsertId();
  }

  /**
   * Executes a SELECT query and returns the value of a single column.
   *
   * Useful for scalar lookups like COUNT(*), MAX(), etc.
   *
   * @param string $query The SQL query.
   * @param array $params Parameters to bind.
   * @param int $columnIndex Index of the column (default: 0).
   * @return mixed The column value or null.
   */
  public function fetchColumn(string $query, array $params = [], int $columnIndex = 0): mixed
  {
    $stm = $this->prepareStatement($query, $params);
    return $stm->execute() ? $stm->fetchColumn($columnIndex) : null;
  }

  /**
   * Executes a SELECT query and returns an array of hydrated entities.
   *
   * @param string $query The SQL SELECT query.
   * @param array $params Parameters to bind.
   * @return array<object> List of hydrated entities.
   */
  public function fetchAllEntities(string $query, array $params = []): array
  {
    $stm = $this->prepareStatement($query, $params);
    $stm->setFetchMode(PDO::FETCH_ASSOC);

    return $stm->execute()
      ? array_map([$this, 'hydrateWithCasting'], $stm->fetchAll())
      : [];
  }

  /**
   * Prepares a PDOStatement and binds parameters with automatic type resolution.
   *
   * @param string $query  The SQL query.
   * @param array  $params Parameters to bind (associative).
   * @return PDOStatement Prepared PDO statement.
   */
  private function prepareStatement(string $query, array $params = []): PDOStatement
  {
    $stm = $this->database->prepare($query);
    foreach ($params as $key => $param) {
      $param_type = gettype($param);
      $pdo_type = match (true) {
        $param_type === 'integer' => PDO::PARAM_INT,
        $param_type === 'boolean' => PDO::PARAM_BOOL,
        $param_type === 'null'    => PDO::PARAM_NULL,
        default                   => PDO::PARAM_STR,
      };
      $stm->bindValue($key, $param, $pdo_type);
    }
    return $stm;
  }

  /**
   * Hydrates a single entity from an associative array row,
   * casting values to match the declared property types.
   *
   * @param array $rawRow The raw row from the database (associative).
   * @return object The hydrated entity.
   */
  private function hydrateWithCasting(array $rawRow): object
  {
    $entity = new ($this->entityClass)();
    $reflection = new \ReflectionClass($this->entityClass);

    foreach ($rawRow as $field => $value) {
      if (!$reflection->hasProperty($field)) {
        continue;
      }

      $property = $reflection->getProperty($field);
      $type = $property->getType()?->getName();

      $converted = match ($type) {
        \DateTime::class => new \DateTime($value),
        'int'            => (int) $value,
        'float'          => (float) $value,
        'bool'           => (bool) $value,
        default          => $value
      };

      $property->setAccessible(true);
      $property->setValue($entity, $converted);
    }

    return $entity;
  }
}
