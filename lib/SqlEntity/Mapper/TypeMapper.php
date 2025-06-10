<?php

namespace AWSD\SqlEntity\Mapper;

use ReflectionProperty;
use AWSD\SqlEntity\Attribute\Type;
use AWSD\SqlEntity\Enum\SqlDialect;
use AWSD\SqlEntity\Enum\EntityType;
use AWSD\SqlEntity\Mapper\Sgbd\MysqlMapper;
use AWSD\SqlEntity\Mapper\Sgbd\PostgresMapper;
use AWSD\SqlEntity\Mapper\Sgbd\SqliteMapper;

/**
 * TypeMapper
 *
 * Central component responsible for mapping a property (ReflectionProperty) of an entity
 * to a SQL type and constraint string based on the current SQL dialect (MySQL, PostgreSQL, SQLite).
 * Delegates formatting logic to dialect-specific mappers.
 */
class TypeMapper
{
  /**
   * Current SQL dialect used for mapping (resolved from environment).
   *
   * @var SqlDialect
   */
  private SqlDialect $sqlDialect;

  /**
   * Metadata extracted from the #[Type] attribute on the property, if present.
   *
   * @var Type|null
   */
  private ?Type $metadata;

  /**
   * The resolved entity-level type for this property (from attribute or PHP type).
   *
   * @var EntityType
   */
  private EntityType $typeSql;

  /**
   * The SGBD-specific mapper responsible for formatting SQL type and constraints.
   *
   * @var object
   */
  private object $sgbdMapper;

  /**
   * @param ReflectionProperty $prop The property to analyze and map.
   */
  public function __construct(protected ReflectionProperty $prop)
  {
    $this->sqlDialect = SqlDialect::fromEnv($_ENV["DB_DRIVER"]);
    $this->metadata =  $this->getMetadata();
    $this->typeSql = $this->getTypeSql();
    $this->sgbdMapper = $this->getSgbdMapper();
  }

  /**
   * Returns the SQL column type string (e.g. VARCHAR(255), TEXT, etc.).
   *
   * @return string
   */
  public function getSqlType(): string
  {
    return $this->sgbdMapper->getType();
  }

  /**
   * Returns the SQL constraints for the column (e.g. PRIMARY KEY, NOT NULL, DEFAULT ...).
   *
   * @return string
   */
  public function getSqlConstraints(): string
  {
    return $this->sgbdMapper->getConstraints();
  }

  /**
   * Extracts the #[Type] attribute instance from the property, if present.
   *
   * @return Type|null
   */
  private function getMetadata(): ?Type
  {
    $attributes = $this->prop->getAttributes(Type::class);
    return $attributes ? $attributes[0]?->newInstance() : null;
  }

  /**
   * Resolves the EntityType (internal logical type) from the attribute or PHP type.
   *
   * @return EntityType
   */
  private function getTypeSql(): EntityType
  {
    if ($this->metadata && $this->metadata->type !== null) {
      return $this->metadata->type;
    }

    $phpType = $this->prop->getType()?->getName();
    return EntityType::fromPhp($phpType);
  }

  /**
   * Returns the SGBD-specific mapper instance based on the SQL dialect.
   *
   * @return object
   */
  private function getSgbdMapper(): object
  {
    return match ($this->sqlDialect) {
      SqlDialect::PGSQL  => new PostgresMapper($this->metadata, $this->typeSql),
      SqlDialect::SQLITE => new SqliteMapper($this->metadata, $this->typeSql),
      SqlDialect::MYSQL  => new MysqlMapper($this->metadata, $this->typeSql),
    };
  }
}
