<?php

namespace AWSD\SqlEntity\Query;

use AWSD\SqlEntity\Mapper\TypeMapper;
use DateTime;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractQuery implements QueryInterface
{

  protected object $entity;
  protected ReflectionClass $reflection;
  protected string $tableName;

  public function __construct(object $entity)
  {
    $this->entity = $entity;
    $this->reflection =  new ReflectionClass($this->entity);
    $this->tableName = $this->resolveTableName();
  }

  protected function resolveTableName(): string
  {
    $name = $this->reflection->getShortName();
    return strtolower($name) . 's';
  }

  protected function getEntityProperties(): array
  {
    return $this->reflection->getProperties();
  }

  protected function getSqlColumns(): array
  {
    $columns = [];
    foreach ($this->getEntityProperties() as $prop) {
      $columns[$prop->getName()] = $this->getSqlColumnDefinition($prop);
    }
    return $columns;
  }

  protected function getSqlColumnDefinition(ReflectionProperty $prop): string
  {
    $typeMapper = new TypeMapper($prop);
    return $typeMapper->getSqlType() . ' ' . $typeMapper->getSqlConstraints();
  }
}
