<?php

namespace AWSD\Schema\Mapper;

use AWSD\Schema\Enum\SqlDialect;

abstract class AbstractMapper
{

  /**
   * Current SQL dialect used for mapping (resolved from environment).
   *
   * @var SqlDialect
   */
  protected SqlDialect $sqlDialect;

  /**
   * The SGBD-specific mapper responsible for formatting SQL type and constraints.
   *
   * @var object
   */
  protected object $sgbdMapper;

  public function __construct()
  {
    $this->sqlDialect = SqlDialect::fromEnv($_ENV["DB_DRIVER"]);
  }

  /**
   * Centralizes mapping from dialect to implementation.
   *
   * @param array<string, object> $implementations
   *        Array keyed by 'pgsql', 'sqlite', 'mysql'
   * @return object
   */
  protected function getSgbdImplementation(array $implementations): object
  {
    return match ($this->sqlDialect) {
      SqlDialect::PGSQL  => $implementations['pgsql'],
      SqlDialect::SQLITE => $implementations['sqlite'],
      SqlDialect::MYSQL  => $implementations['mysql'],
    };
  }
}
