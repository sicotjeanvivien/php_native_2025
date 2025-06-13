<?php

namespace AWSD\Schema\Mapper\SGBD\SQLite;

use AWSD\Schema\Mapper\SGBD\TriggerMapperInterface;

/**
 * Class TriggerMapper (SQLite)
 *
 * Implements the TriggerMapperInterface for SQLite.
 *
 * This mapper explicitly disables trigger generation, as SQLite has
 * limited or incompatible trigger support with this ORM's design.
 * All trigger-related methods return empty strings.
 *
 * Purpose:
 * - Ensure consistent fallback when the ORM targets SQLite
 * - Maintain interface compliance without enabling unsupported features
 *
 * Used by TriggerOrchestrator to determine trigger capability at runtime.
 */
final class TriggerMapper implements TriggerMapperInterface
{
  /**
   * Indicates that trigger generation is not supported for SQLite.
   *
   * @return bool Always false.
   */
  public function supportsTriggers(): bool
  {
    return false;
  }

  /**
   * Returns an empty string; SQLite trigger functions are not generated.
   *
   * @param string $tableName Ignored.
   * @param string $column Ignored.
   * @return string Always empty.
   */
  public function getFunctionName(string $tableName, string $column): string
  {
    return "";
  }

  /**
   * Returns an empty string; SQLite trigger function body is unsupported.
   *
   * @param string $column Ignored.
   * @return string Always empty.
   */
  public function getFunctionBody(string $column): string
  {
    return "";
  }

  /**
   * Returns an empty string; SQLite trigger declaration is disabled.
   *
   * @param string $tableName Ignored.
   * @param string $functionName Ignored.
   * @return string Always empty.
   */
  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return "";
  }
}
