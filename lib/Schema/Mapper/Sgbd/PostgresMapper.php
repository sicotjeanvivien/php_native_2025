<?php

namespace AWSD\Schema\Mapper\Sgbd;

use AWSD\Schema\Enum\EntityType;

/**
 * PostgresMapper
 *
 * Maps an entity field to PostgreSQL SQL types and constraints.
 * Handles serial auto-increment detection, jsonb types, and default timestamp behavior.
 */
class PostgresMapper extends AbstractSgbdMapper
{
  /**
   * Resolves the PostgreSQL column type from the entity field type.
   * Special case: returns 'serial' if type is INT and marked as primary + autoincrement.
   *
   * @return string The corresponding PostgreSQL column type.
   */
  public function getType(): string
  {
    if ($this->isSerialType()) {
      return 'serial';
    }

    return match ($this->typeSql) {
      EntityType::INT       => 'integer',
      EntityType::FLOAT     => 'double precision',
      EntityType::STRING    => 'varchar(255)',
      EntityType::BOOL      => 'boolean',
      EntityType::DATETIME  => 'timestamp',
      EntityType::ARRAY     => 'jsonb',
      EntityType::OBJECT    => 'jsonb',
      EntityType::MIXED     => 'text',
      EntityType::UUID      => 'uuid',
      EntityType::TEXT      => 'text',
      default               => 'text'
    };
  }

  /**
   * Builds the constraint string for the column based on metadata.
   * Includes: PRIMARY KEY, DEFAULT, NULL/NOT NULL.
   *
   * @return string The SQL constraint clause for the field.
   */
  public function getConstraints(): string
  {
    if (!$this->metadata) {
      return '';
    }

    $parts = [];

    if ($this->metadata->primary) {
      $parts[] = 'PRIMARY KEY';
    }

    if ($this->metadata->default !== null && $this->metadata->default !== "ON UPDATE CURRENT_TIMESTAMP") {
      $parts[] = 'DEFAULT ' . $this->quoteDefault($this->metadata->default);
    }

    $parts[] = $this->metadata->nullable ? 'NULL' : 'NOT NULL';

    return implode(' ', $parts);
  }

  /**
   * Determines whether the column should use the 'serial' type in PostgreSQL.
   * This is valid when the type is INT, and the field is both primary and autoincremented.
   *
   * @return bool True if 'serial' should be used instead of 'integer'.
   */
  private function isSerialType(): bool
  {
    return $this->typeSql === EntityType::INT
      && $this->metadata?->primary
      && $this->metadata?->autoincrement;
  }
}
