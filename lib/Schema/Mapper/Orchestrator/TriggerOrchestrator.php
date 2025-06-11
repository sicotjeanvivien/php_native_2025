<?php

namespace AWSD\Schema\Mapper\Orchestrator;

use AWSD\Schema\Mapper\AbstractMapper;
use AWSD\Schema\Mapper\SGBD\MySQL\TriggerMapper as MySQLTriggerMapper;
use AWSD\Schema\Mapper\SGBD\PostgreSQL\TriggerMapper as PostgreSQLTriggerMapper;
use AWSD\Schema\Mapper\SGBD\SQLite\TriggerMapper as SQLiteTriggerMapper;

class TriggerOrchestrator extends AbstractMapper
{

  public function __construct()
  {
    parent::__construct();

    $this->sgbdMapper = $this->getSgbdImplementation([
      'pgsql'  => new PostgreSQLTriggerMapper(),
      'sqlite' => new SQLiteTriggerMapper(),
      'mysql'  => new MySQLTriggerMapper(),
    ]);
  }

  public function isSupported(): bool
  {
    return method_exists($this->sgbdMapper, 'supportsTriggers') && $this->sgbdMapper->supportsTriggers();
  }

  public function isNotSupported(): bool
  {
    return !$this->isSupported();
  }

  public function getFunctionName(string $tableName, string $column): string
  {
    return $this->sgbdMapper->getFunctionName($tableName, $column);
  }

  public function getTriggerFunctionBody(string $column): string
  {
    return $this->sgbdMapper->getFunctionBody($column);
  }

  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return $this->sgbdMapper->getTriggerDeclaration($tableName, $functionName);
  }
}
