<?php

declare(strict_types=1);

namespace AWSD\Database\Schema;

use AWSD\Database\QueryExecutor;
use AWSD\Database\Schema\Query\CreateQuery;
use AWSD\Database\Schema\Query\IndexQuery;
use AWSD\Database\Schema\Query\SelectQuery;
use AWSD\Database\Schema\Query\TriggerQuery;

/**
 * Class EntitySchemaBuilder
 *
 * Provides schema generation and SELECT data access for a given entity.
 *
 * This builder serves two roles:
 * 1. Generates the full SQL schema (DDL) for an entity:
 *    - CREATE TABLE statement
 *    - INDEX definitions
 *    - TRIGGER declarations (PostgreSQL only)
 * 2. Executes SELECT queries on the corresponding table using field and condition specifications,
 *    with hydration of entities via the QueryExecutor.
 *
 * Dependencies:
 * - The entity must be annotated with attributes such as #[Type], #[Index], #[Trigger]
 * - Assumes table names and fields are derived from the entity structure
 *
 * Example usage:
 *   $builder = new EntitySchemaBuilder($user);
 *   $sql = $builder->create(); // generates full DDL (CREATE + INDEX + TRIGGER)
 *   $rows = $builder->findAll(['id', 'email'], ['status' => 'active']);
 */
class EntitySchemaBuilder
{
  /**
   * @var object The entity instance for which the schema is generated or queried.
   */
  private object $entity;

  /**
   * @var QueryExecutor The internal query executor bound to the entity class.
   */
  private QueryExecutor $queryExecutor;

  /**
   * Constructor
   *
   * Initializes the builder with the given entity instance and prepares a query executor.
   *
   * @param object $entity The entity instance (e.g. new User()).
   */
  public function __construct(object $entity)
  {
    $this->entity = $entity;
    $this->queryExecutor = new QueryExecutor(($entity)::class);
  }

  /**
   * Generates the full SQL schema for the entity.
   *
   * This includes:
   * - CREATE TABLE statement
   * - Index creation statements
   * - Trigger declarations (only on supported platforms)
   *
   * @return string The complete SQL schema script for the entity.
   */
  public function create(): string
  {
    $queries = [
      (new CreateQuery($this->entity::class))->generateSql(),
      (new IndexQuery($this->entity::class))->generateSql(),
      (new TriggerQuery($this->entity::class))->generateSql(),
    ];
    return implode("\n\n", $queries);
  }

  /**
   * Executes a SELECT * query with optional field filtering and WHERE conditions.
   *
   * Fields and conditions are passed to the SelectQuery generator,
   * and the results are hydrated using QueryExecutor.
   *
   * @param array $fields The list of fields to select (empty = all).
   * @param array $where An associative array of WHERE conditions (field => value).
   * @return array<object> An array of hydrated entity objects.
   */
  public function findAll(array $fields = [], array $where = []): array
  {
    $selectQuery = new SelectQuery($this->entity::class);
    $sql = $selectQuery->setFields($fields)->setWhere($where)->generateSql();
    $params = $selectQuery->getParams();
    return $this->queryExecutor->fetchAllEntities($sql, $params);
  }

  /**
   * Placeholder for future implementation of dynamic SELECT with conditions.
   *
   * Should provide more flexible criteria and comparison support.
   *
   * @return void
   */
  public function findOneBy(array $fields = [], array $where = []): ?object
  {
    $selectQuery = new SelectQuery($this->entity::class);
    $sql = $selectQuery->setFields($fields)->setWhere($where)->setLimit(1)->generateSql();
    $params = $selectQuery->getParams();
    return $this->queryExecutor->executeQuery($sql, $params);
  }
}
