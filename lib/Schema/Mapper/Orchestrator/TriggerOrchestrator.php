<?php

namespace AWSD\Schema\Mapper\Orchestrator;

use AWSD\Schema\Mapper\AbstractMapper;
use AWSD\Schema\Mapper\SGBD\MySQL\TriggerMapper as MySQLTriggerMapper;
use AWSD\Schema\Mapper\SGBD\PostgreSQL\TriggerMapper as PostgreSQLTriggerMapper;
use AWSD\Schema\Mapper\SGBD\SQLite\TriggerMapper as SQLiteTriggerMapper;

/**
 * Class TriggerOrchestrator
 *
 * Delegates trigger-related SQL generation to the appropriate SGBD-specific TriggerMapper.
 *
 * This class acts as a strategy dispatcher. Based on the current database driver
 * (e.g. `pgsql`, `mysql`, `sqlite`), it routes trigger logic to the correct implementation.
 *
 * - PostgreSQL: full support for BEFORE UPDATE triggers (with function + declaration)
 * - MySQL / SQLite: trigger generation is disabled (noop mappers)
 *
 * Used by `TriggerQuery` to centralize trigger generation logic.
 *
 * Example usage:
 * ```php
 * $orchestrator = new TriggerOrchestrator();
 * if ($orchestrator->isSupported()) {
 *     $sql = $orchestrator->getTriggerDeclaration('users', 'set_users_updated_at');
 * }
 * ```
 *
 * @see \AWSD\Schema\Query\TriggerQuery
 */
class TriggerOrchestrator extends AbstractMapper
{
  /**
   * Constructor
   *
   * Initializes the orchestrator with the correct TriggerMapper
   * based on the current database driver (`$_ENV['DB_DRIVER']`).
   */
  public function __construct()
  {
    parent::__construct();

    $this->sgbdMapper = $this->getSgbdImplementation([
      'pgsql'  => new PostgreSQLTriggerMapper(),
      'sqlite' => new SQLiteTriggerMapper(),
      'mysql'  => new MySQLTriggerMapper(),
    ]);
  }

  /**
   * Checks whether the current SGBD supports trigger generation.
   *
   * @return bool True if triggers are supported (e.g. PostgreSQL), false otherwise.
   */
  public function isSupported(): bool
  {
    return method_exists($this->sgbdMapper, 'supportsTriggers') && $this->sgbdMapper->supportsTriggers();
  }

  /**
   * Shortcut for checking if triggers are not supported.
   *
   * @return bool True if triggers are unsupported or disabled.
   */
  public function isNotSupported(): bool
  {
    return !$this->isSupported();
  }

  /**
   * Returns the SQL-safe function name to be used for a trigger.
   *
   * @param string $tableName The table name.
   * @param string $column The column for which the trigger is defined.
   * @return string The function name (e.g. set_users_updated_at).
   */
  public function getFunctionName(string $tableName, string $column): string
  {
    return $this->sgbdMapper->getFunctionName($tableName, $column);
  }

  /**
   * Returns the PL/pgSQL body of the trigger function.
   *
   * @param string $column The column being updated.
   * @return string The body of the trigger function.
   */
  public function getTriggerFunctionBody(string $column): string
  {
    return $this->sgbdMapper->getFunctionBody($column);
  }

  /**
   * Returns the final CREATE TRIGGER SQL statement for the table.
   *
   * @param string $tableName The table the trigger applies to.
   * @param string $functionName The associated trigger function name.
   * @return string The SQL CREATE TRIGGER statement.
   */
  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return $this->sgbdMapper->getTriggerDeclaration($tableName, $functionName);
  }
}
