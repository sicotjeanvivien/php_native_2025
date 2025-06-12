<?php

namespace AWSD\Schema\Query;

use AWSD\Schema\Query\Component\GroupByComponent;
use AWSD\Schema\Query\Component\HavingComponent;
use AWSD\Schema\Query\Component\JoinComponent;
use AWSD\Schema\Query\Component\OrderByComponent;
use AWSD\Schema\Query\Component\WhereComponent;

final class SelectQuery extends AbstractQuery implements QueryInterface
{
  private array $params;
  private array $fields;
  private array $componentsSql;

  public function __construct(object $entity)
  {
    parent::__construct($entity, []);
    $this->fields =  [];
    $this->params = [];
    $this->componentsSql = [
      "JOIN" => "",
      "WHERE" => "",
      "GROUP_BY" => "",
      "HAVING" => "",
      "ORDER_BY" => "",
      "LIMIT" => "",
      "OFFSET" => "",
    ];
  }

  public function generateSql(): string
  {
    return $this->getQuery($this->getFields(), $this->getcomponentsSql());
  }

  public function getParams(): array
  {
    return $this->params;
  }

  public function setFields(array $fields): self
  {
    $this->fields = $fields;
    return $this;
  }

  public function setJoin(array $joins): self
  {
    $this->componentsSql["JOIN"] = (new JoinComponent($joins))->build();
    return $this;
  }

  public function setWhere(array $conditions): self
  {
    $where = (new WhereComponent($conditions))->build();
    $this->componentsSql['WHERE'] = $where['sql'];
    $this->params = array_merge($this->params, $where['params']);
    return $this;
  }

  public function setGroupBy(array $groupBy): self
  {
    $this->componentsSql["GROUP_BY"] = (new GroupByComponent($groupBy))->build();
    return $this;
  }

  public function setHaving(array $having): self
  {
    $this->componentsSql["HAVING"] = (new HavingComponent($having))->build();
    return $this;
  }

  public function setOrderBy(array $orderBy): self
  {
    $this->componentsSql["ORDER_BY"] = (new OrderByComponent($orderBy))->build();
    return $this;
  }

  public function setLimit(array $limit): self
  {
    $this->componentsSql["LIMIT"] = (new OrderByComponent($limit))->build();
    return $this;
  }

  public function setOffset(array $offset): self
  {
    $this->componentsSql["OFFSET"] = (new OrderByComponent($offset))->build();
    return $this;
  }

  private function getQuery(string $fields, string $componentsSql): string
  {
    return <<<SQL
    SELECT $fields FROM $this->tableName $componentsSql
    SQL;
  }

  private function getFields(): string
  {
    return  empty($this->fields) ? '*' : implode(', ', $this->fields);
  }

  private function getcomponentsSql(): string
  {
    $componentsSqlFilter = array_filter($this->componentsSql, fn($v) => !empty($v));
    return implode(' ', array_filter($componentsSqlFilter));
  }
}
