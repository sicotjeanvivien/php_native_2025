<?php

namespace AWSD\Schema\Mapper\Sgbd;

use AWSD\Schema\Enum\EntityType;

/**
 * MysqlMapper
 *
 * Responsible for mapping an entity field to its SQL type and constraints for MySQL.
 * Supports common MySQL features such as AUTO_INCREMENT, TINYINT(1) for booleans,
 * and CURRENT_TIMESTAMP handling for DATETIME/TIMESTAMP columns.
 */
class MysqlMapper extends AbstractSgbdMapper
{
  /**
   * Resolves the MySQL type for the given EntityType.
   *
   * @return string The corresponding MySQL column type (e.g., VARCHAR(255), INT, etc.).
   */
  public function getType(): string
  {
    return match ($this->typeSql) {
      EntityType::INT       => 'INT',
      EntityType::FLOAT     => 'DOUBLE',
      EntityType::STRING    => 'VARCHAR(255)',
      EntityType::BOOL      => 'TINYINT(1)',
      EntityType::DATETIME  => $this->getDatetimeType(),
      EntityType::ARRAY     => 'JSON',
      EntityType::OBJECT    => 'JSON',
      EntityType::MIXED     => 'TEXT',
      EntityType::UUID      => 'CHAR(36)',
      EntityType::TEXT      => 'TEXT',
      default               => 'TEXT'
    };
  }

  /**
   * Builds the constraint string for the column based on metadata.
   * Includes: AUTO_INCREMENT, PRIMARY KEY, DEFAULT, NULL/NOT NULL.
   *
   * @return string The SQL constraint clause for this field.
   */
  public function getConstraints(): string
  {
    if (!$this->metadata) return '';

    $parts = [];

    if ($this->metadata->autoincrement) {
      $parts[] = 'AUTO_INCREMENT';
    }

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
   * Determines whether the DATETIME field should be defined as TIMESTAMP
   * when using CURRENT_TIMESTAMP as default.
   *
   * @return string 'TIMESTAMP' or 'DATETIME'
   */
  private function getDatetimeType(): string
  {
    return strtoupper($this->metadata?->default ?? '') === 'CURRENT_TIMESTAMP'
      ? 'TIMESTAMP'
      : 'DATETIME';
  }
}
