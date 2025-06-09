<?php

namespace AWSD\SqlEntity\Mapper\Sgbd;

use AWSD\SqlEntity\Enum\TypeEnum;

class PostgresMapper extends AbstractSgbdMapper
{
  public function getType(): string
  {

    if ($this->isSerialType()) return 'serial';

    return match ($this->typeSql) {
      TypeEnum::INT       => 'integer',
      TypeEnum::FLOAT     => 'double precision',
      TypeEnum::STRING    => 'varchar',
      TypeEnum::BOOL      => 'boolean',
      TypeEnum::DATETIME  => 'timestamp',
      TypeEnum::ARRAY     => 'jsonb',
      TypeEnum::OBJECT    => 'jsonb',
      TypeEnum::MIXED     => 'text',
      TypeEnum::UUID      => 'uuid',
      TypeEnum::TEXT      => 'text',
      default             => 'text'
    };
  }

  public function getConstraints(): string
  {
    if (!$this->metadata) return '';

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

  private function isSerialType(): bool
  {
    return $this->typeSql === TypeEnum::INT
      && $this->metadata?->primary
      && $this->metadata?->autoincrement;
  }
}
