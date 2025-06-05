<?php

namespace AWSD\Database\Query;

class SqlQueryGenerator
{
  private CreateQuery $createQuery;
  private SelectQuery $selectQuery;
  private InsertQuery $insertQuery;
  private UpdateQuery $updateQuery;
  private DeleteQuery $deleteQuery;


  public function __construct(private object $entity)
  {
    $this->createQuery = new CreateQuery($this->entity);
    $this->selectQuery = new SelectQuery($this->entity);
  }

  public function create(): string
  {
    return $this->createQuery->generateSql();
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
