<?php

namespace AWSD\SqlEntity\Mapper;

use AWSD\SqlEntity\Attribute\Type;
use AWSD\SqlEntity\Enum\SqlDialectEnum;
use AWSD\SqlEntity\Enum\TypeEnum;
use AWSD\SqlEntity\Mapper\Sgbd\MysqlMapper;
use AWSD\SqlEntity\Mapper\Sgbd\PostgresMapper;
use AWSD\SqlEntity\Mapper\Sgbd\SqliteMapper;
use ReflectionProperty;

class TypeMapper
{

  private SqlDialectEnum $sqlDialect;
  private ?Type $metadata;
  private object $sgbdMapper;
  private TypeEnum $typeSql;

  public function __construct(protected ReflectionProperty $prop)
  {
    $this->sqlDialect = SqlDialectEnum::fromEnv();
    $this->metadata =  $this->getMetadata();
    $this->typeSql = $this->getTypeSql();
    $this->sgbdMapper = $this->getSgbdMapper();
  }

  public function getSqlType(): string
  {
    return $this->sgbdMapper->getType();
  }

  public function getSqlConstraints(): string
  {
    return $this->sgbdMapper->getConstraints();
  }

  private function getMetadata(): ?Type
  {
    $attributes = $this->prop->getAttributes(Type::class);
    return $attributes ? $attributes[0]?->newInstance() : null;
  }

  private function getTypeSql(): TypeEnum
  {
    if ($this->metadata && $this->metadata->type !== null) {
      return $this->metadata->type;
    }

    $phpType = $this->prop->getType()?->getName();
    return TypeEnum::fromPhp($phpType);
  }

  private function getSgbdMapper(): object
  {
    return match ($this->sqlDialect) {
      SqlDialectEnum::PGSQL  => new PostgresMapper($this->metadata, $this->typeSql),
      SqlDialectEnum::SQLITE => new SqliteMapper($this->metadata, $this->typeSql),
      SqlDialectEnum::MYSQL  => new MysqlMapper($this->metadata, $this->typeSql),
    };
  }
}
