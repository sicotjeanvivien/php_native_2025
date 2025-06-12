<?php

namespace AWSD\Schema;

use AWSD\Database\QueryExecutor;
use AWSD\Schema\Query\CreateQuery;
use AWSD\Schema\Query\IndexQuery;
use AWSD\Schema\Query\SelectQuery;
use AWSD\Schema\Query\TriggerQuery;

class EntitySchemaBuilder
{

  private object $entity;

  private QueryExecutor $queryExecutor;

  public function __construct(object $entity) {
    $this->entity = $entity;
    $this->queryExecutor = new QueryExecutor($entity);
  }

  public function create(): string
  {
    $queries = [
      (new CreateQuery($this->entity))->generateSql(),
      (new IndexQuery($this->entity))->generateSql(),
      (new TriggerQuery($this->entity))->generateSql(),
    ];
    return implode("\n\n", $queries);
  }

  public function findAll(array $fields = [], array $where = []): array
  {
    $selectQuery = new SelectQuery($this->entity);
    $sql = $selectQuery->setFields($fields)->generateSql();
    $params = $selectQuery->getParams();
    return $this->queryExecutor->executeQuery($sql, $params);
  }

  public function findBy()
  {
    # code...
  }

}
