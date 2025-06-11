<?php

namespace AWSD\Schema;

use AWSD\Schema\Query\CreateQuery;
use AWSD\Schema\Query\IndexQuery;
use AWSD\Schema\Query\TriggerQuery;

class EntitySchemaBuilder
{

  public function __construct(private object $entity) {}

  public function create(): string
  {
    $queries = [
      (new CreateQuery($this->entity))->generateSql(),
      (new IndexQuery($this->entity))->generateSql(),
      (new TriggerQuery($this->entity))->generateSql(),
    ];
    return implode("\n\n", $queries);
  }

  public function select(): string
  {
    // TODO: Implement later
    throw new \LogicException('Select not implemented yet.');
  }

  public function insert(): string
  {
    // TODO
    throw new \LogicException('Insert not implemented yet.');
  }

  public function update(): string
  {
    // TODO
    throw new \LogicException('Update not implemented yet.');
  }

  public function delete(): string
  {
    // TODO
    throw new \LogicException('Delete not implemented yet.');
  }
}
