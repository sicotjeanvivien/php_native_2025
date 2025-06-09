<?php

namespace AWSD\SqlEntity\Mapper\Sgbd;

use AWSD\SqlEntity\Enum\TypeEnum;

class SqliteMapper extends AbstractSgbdMapper
{
  public function getType(): string
  {
    return match ($this->typeSql) {
      TypeEnum::INT        => 'INTEGER',
      TypeEnum::FLOAT      => 'REAL',
      TypeEnum::STRING     => 'TEXT',
      TypeEnum::BOOL       => 'INTEGER',
      TypeEnum::DATETIME   => 'TEXT',
      TypeEnum::ARRAY      => 'TEXT',
      TypeEnum::OBJECT     => 'TEXT',
      TypeEnum::MIXED      => 'TEXT',
      TypeEnum::UUID       => 'TEXT',
      TypeEnum::TEXT       => 'TEXT',
      default              => 'TEXT'
    };
  }

  public function getConstraints(): string
  {
    if (!$this->metadata) return '';
    if ($this->isPrimaryKeyAutoincrement()) return 'PRIMARY KEY AUTOINCREMENT';

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


  private function isPrimaryKeyAutoincrement(): bool
  {
    return $this->typeSql === TypeEnum::INT &&
      $this->metadata->primary &&
      $this->metadata->autoincrement;
  }
}
