<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\Orchestrator;

use ReflectionProperty;
use AWSD\Database\Schema\Attribute\Type;
use AWSD\Database\Schema\Enum\EntityType;
use AWSD\Database\Schema\Mapper\SGBD\MySQL\TypeMapper as MySQLTypeMapper;
use AWSD\Database\Schema\Mapper\SGBD\PostgreSQL\TypeMapper as PostgreSQLTypeMapper;
use AWSD\Database\Schema\Mapper\SGBD\SQLite\TypeMapper as SQLiteTypeMapper;

/**
 * TypeOrchestrator
 *
 * Central component responsible for mapping a property (ReflectionProperty) of an entity
 * to a SQL type and constraint string based on the current SQL dialect (MySQL, PostgreSQL, SQLite).
 * Delegates formatting logic to dialect-specific mappers.
 */
final class TypeOrchestrator extends AbstractOrchestrator
{

  protected ReflectionProperty $prop;

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
   * @param ReflectionProperty $prop The property to analyze and map.
   */
  public function __construct(ReflectionProperty $prop, ?Type $metadata)
  {
    parent::__construct();
    $this->prop = $prop;
    $this->metadata =  $metadata;
    $this->typeSql = $this->getTypeSql();
    $this->sgbdMapper = $this->getSgbdImplementation([
      'pgsql'  => new PostgreSQLTypeMapper($this->metadata, $this->typeSql),
      'sqlite' => new SQLiteTypeMapper($this->metadata, $this->typeSql),
      'mysql'  => new MySQLTypeMapper($this->metadata, $this->typeSql),
    ]);
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
}
