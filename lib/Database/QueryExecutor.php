<?php

namespace AWSD\Database;

use PDO;
use PDOStatement;

/**
 * Class QueryExecutor
 *
 * Provides methods to safely prepare and execute SQL queries using PDO,
 * with optional parameter binding and entity hydration.
 */
class QueryExecutor
{

  /**
   * @var PDO The database connection instance.
   */
  protected PDO $database;

  /**
   * @var string The fully qualified class name of the entity associated with the repository.
   */
  protected readonly string $entityClass;

  /**
   * QueryExecutor constructor.
   *
   * Initializes the database connection.
   */
  public function __construct(string $entityClass = '')
  {
    $this->database = Database::getInstance();
    $this->entityClass = $entityClass;
  }

  /**
   * Executes an INSERT, UPDATE, or DELETE SQL statement.
   *
   * @param string $query The SQL query to execute.
   * @param array $params An associative array of query parameters.
   * @return int The number of affected rows.
   */
  public function executeNonQuery(string $query, array $params = []): int
  {
    $stm = $this->prepareStatement($query, $params);
    return $stm->execute() ? $stm->rowCount() : 0;
  }

  /**
   * Executes a SQL query with optional parameters.
   *
   * @param string $query The SQL query to execute.
   * @param array $params An associative array of query parameters.
   * @return mixed The query result, either a single entity, an array of entities, or null.
   */
  public function executeQuery(string $query, array $params = []): mixed
  {
    $stm = $this->prepareStatement($query, $params);

    if ($stm->execute()) {
      $fetchMode = $this->entityClass ? PDO::FETCH_CLASS : PDO::FETCH_ASSOC;
      $stm->setFetchMode($fetchMode, $this->entityClass ?: null);

      return match (true) {
        $stm->rowCount() === 1 => $stm->fetch(),
        $stm->rowCount() > 1   => $stm->fetchAll(),
        default                => null
      };
    }
    return null;
  }

  /**
   * Returns the ID of the last inserted row as a string.
   *
   * This is typically used after INSERT queries to retrieve the auto-incremented primary key.
   *
   * @return string The last inserted ID (always returned as string by PDO).
   */
  public function lastInsertId(): string
  {
    return $this->database->lastInsertId();
  }

  /**
   * Executes a SELECT query and returns a single column value.
   *
   * Useful for queries like SELECT COUNT(*) or scalar lookups.
   *
   * @param string $query The SQL query to execute.
   * @param array $params An associative array of query parameters.
   * @param int $columnIndex The index of the column to fetch (default: 0).
   * @return mixed The column value or false if no result.
   */
  public function fetchColumn(string $query, array $params = [], int $columnIndex = 0): mixed
  {
    $stm = $this->prepareStatement($query, $params);
    return $stm->execute() ? $stm->fetchColumn($columnIndex) : null;
  }

  /**
   * Executes a SELECT query and returns all values from a single column.
   *
   * Useful for retrieving a list of scalar values like filenames, IDs, etc.
   *
   * @param string $query The SQL query to execute.
   * @param array $params An associative array of query parameters.
   * @param int $columnIndex The index of the column to fetch (default: 0).
   * @return array The list of column values.
   */
  public function fetchAllColumn(string $query, array $params = [], int $columnIndex = 0): array
  {
    $stm = $this->prepareStatement($query, $params);
    return $stm->execute() ? $stm->fetchAll(PDO::FETCH_COLUMN, $columnIndex) : [];
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
