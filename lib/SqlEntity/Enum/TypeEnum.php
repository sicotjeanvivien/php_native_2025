<?php

namespace AWSD\SqlEntity\Enum;

enum TypeEnum: string
{
  // Types de base PHP
  case INT          = 'int';
  case FLOAT        = 'float';
  case STRING       = 'string';
  case BOOL         = 'bool';
  case DATETIME     = 'DateTime';
  case ARRAY        = 'array';
  case OBJECT       = 'object';
  case MIXED        = 'mixed';
  case UUID         = 'uuid';
  case PRIMARY_INT  = 'primaryInt';
  case TEXT         = 'text';

  public static function fromPhp(?string $type): self
  {
    return match ($type) {
      'int'      => self::INT,
      'float'    => self::FLOAT,
      'string'   => self::STRING,
      'bool'     => self::BOOL,
      'DateTime' => self::DATETIME,
      'array'    => self::ARRAY,
      'object'   => self::OBJECT,
      default    => self::MIXED,
    };
  }

  public static function fromAttribute(string $type): self
  {
    return self::tryFrom($type) ?? throw new \InvalidArgumentException("Unknown type: $type");
  }
}
