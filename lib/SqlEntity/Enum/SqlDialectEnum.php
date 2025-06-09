<?php

namespace AWSD\SqlEntity\Enum;

enum SqlDialectEnum: string
{
  case MYSQL  = 'mysql';
  case PGSQL  = 'pgsql';
  case SQLITE = 'sqlite';

  public static function fromEnv(): self
  {
    return match ($_ENV['DB_DRIVER']) {
      'mysql'  => self::MYSQL,
      'pgsql'  => self::PGSQL,
      'sqlite' => self::SQLITE,
      default  => throw new \RuntimeException("Unsupported DB driver: " . ($_ENV['DB_DRIVER'] ?? 'undefined')),
    };
  }
}
