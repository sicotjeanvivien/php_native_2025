<?php

namespace AWSD\Schema\Mapper\SGBD\SQLite;

use AWSD\Schema\Enum\EntityType;
use AWSD\Schema\Mapper\SGBD\AbstractTypeMapper;
use AWSD\Schema\Mapper\SGBD\TypeMapperInterface;

/**
 * SqliteMapper
 *
 * Maps an entity field to its corresponding SQLite type and SQL constraints.
 * SQLite uses dynamic typing, so most types are mapped to TEXT, REAL, or INTEGER.
 * Handles the special case for 'INTEGER PRIMARY KEY AUTOINCREMENT'.
 */
class TypeMapper extends AbstractTypeMapper implements TypeMapperInterface
{
  /**
   * Resolves the SQLite column type for the given logical entity type.
   *
   * @return string SQLite-compatible column type (e.g. TEXT, REAL, INTEGER).
   */
  public function getType(): string
  {
    return match ($this->typeSql) {
      EntityType::INT        => 'INTEGER',
      EntityType::FLOAT      => 'REAL',
      EntityType::STRING     => 'TEXT',
      EntityType::BOOL       => 'INTEGER',
      EntityType::DATETIME   => 'TEXT',
      EntityType::ARRAY      => 'TEXT',
      EntityType::OBJECT     => 'TEXT',
      EntityType::MIXED      => 'TEXT',
      EntityType::UUID       => 'TEXT',
      EntityType::TEXT       => 'TEXT',
      default                => 'TEXT'
    };
  }

  /**
   * Builds the constraint string for the column based on metadata.
   * Handles SQLite's strict rule for autoincrement: must be INTEGER PRIMARY KEY AUTOINCREMENT.
   *
   * @return string The SQL constraint clause for the field.
   */
  public function getConstraints(): string
  {
    if (!$this->metadata) {
      return '';
    }

    // Special case for autoincrementing primary key
    if ($this->isPrimaryKeyAutoincrement()) {
      return 'PRIMARY KEY AUTOINCREMENT';
    }

    $parts = [];

    if ($this->metadata->primary) {
      $parts[] = 'PRIMARY KEY';
    }

    if ($this->metadata->default !== null) {
      $parts[] = 'DEFAULT ' . $this->quoteDefault($this->metadata->default);
    }

    $parts[] = $this->metadata->nullable ? 'NULL' : 'NOT NULL';

    return implode(' ', $parts);
  }

  /**
   * Determines whether the field should be defined as INTEGER PRIMARY KEY AUTOINCREMENT,
   * which is a special constraint in SQLite that implies rowid-based indexing.
   *
   * @return bool True if the field is an autoincrementing primary key on an INTEGER column.
   */
  private function isPrimaryKeyAutoincrement(): bool
  {
    return $this->typeSql === EntityType::INT
      && $this->metadata->primary
      && $this->metadata->autoincrement;
  }
}
