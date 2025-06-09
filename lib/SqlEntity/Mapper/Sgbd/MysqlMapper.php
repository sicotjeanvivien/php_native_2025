<?php

namespace AWSD\SqlEntity\Mapper\Sgbd;

class MysqlMapper extends AbstractSgbdMapper
{
  public function getType(): string
  {
    return match ($this->typeSql) {
      'int'      => 'INT',
      'float'    => 'DOUBLE',
      'string'   => 'VARCHAR(255)',
      'bool'     => 'TINYINT(1)',
      'DateTime' => 'DATETIME',
      'array'    => 'JSON',
      'object'   => 'JSON',
      default    => 'TEXT',
    };
  }

  public function getConstraints(): string {
    return "";
  }
}
