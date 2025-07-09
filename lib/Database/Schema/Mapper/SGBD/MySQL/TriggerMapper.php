<?php

namespace AWSD\Database\Schema\Mapper\SGBD\MySQL;

use AWSD\Database\Schema\Mapper\SGBD\interface\TriggerMapperInterface;

/**
 * Class TriggerMapper (MySQL)
 *
 * Implements the TriggerMapperInterface for MySQL.
 *
 * MySQL's trigger system is not supported by this ORM layer, so this mapper
 * disables trigger-related SQL generation. All trigger methods return empty strings.
 *
 * This class ensures graceful degradation in systems that rely on triggers
 * (like PostgreSQL) when switching to MySQL as the target SGBD.
 *
 * Used internally by the TriggerOrchestrator to detect SGBD capabilities.
 */
final class TriggerMapper implements TriggerMapperInterface
{
  /**
   * Indicates that MySQL is not supported for trigger generation in this ORM.
   *
   * @return bool Always returns false.
   */
  public function supportsTriggers(): bool
  {
    return false;
  }

  /**
   * Returns an empty string, as MySQL triggers are not supported.
   *
   * @param string $tableName Ignored.
   * @param string $column Ignored.
   * @return string Empty string.
   */
  public function getFunctionName(string $tableName, string $column): string
  {
    return "";
  }

  /**
   * Returns an empty string, as MySQL triggers are not supported.
   *
   * @param string $column Ignored.
   * @return string Empty string.
   */
  public function getFunctionBody(string $column): string
  {
    return "";
  }

  /**
   * Returns an empty string, as MySQL triggers are not supported.
   *
   * @param string $tableName Ignored.
   * @param string $functionName Ignored.
   * @return string Empty string.
   */
  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return "";
  }
}
