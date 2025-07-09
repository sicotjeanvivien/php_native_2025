<?php

namespace AWSD\Database\Schema\Mapper\SGBD\PostgreSQL;

use AWSD\Database\Schema\Mapper\SGBD\interface\TriggerMapperInterface;

/**
 * Class TriggerMapper
 *
 * PostgreSQL-specific implementation of TriggerMapperInterface.
 *
 * This mapper defines how to generate:
 * - Trigger function names
 * - Function bodies in PL/pgSQL
 * - Trigger declarations for BEFORE UPDATE triggers
 *
 * Usage context:
 * Used by TriggerOrchestrator to delegate SGBD-specific SQL generation
 * for fields annotated with #[Trigger].
 *
 * Example output:
 * - A function `set_users_updated_at()` that sets `updated_at` on UPDATE
 * - A trigger declaration binding it to the corresponding table
 *
 * @implements TriggerMapperInterface
 * @see AWSD\Database\Schema\Attribute\Trigger
 */
final class TriggerMapper implements TriggerMapperInterface
{

  /**
   * Indicates that PostgreSQL supports trigger features.
   *
   * @return bool Always true for PostgreSQL.
   */
  public function supportsTriggers(): bool
  {
    return true;
  }

  /**
   * Generates the SQL-safe function name for a given table/column pair.
   *
   * @param string $tableName The name of the table.
   * @param string $column The name of the column to update.
   * @return string The trigger function name (e.g. "set_users_updated_at").
   */
  public function getFunctionName(string $tableName, string $column): string
  {
    return "set_{$tableName}_{$column}";
  }

  /**
   * Returns the PL/pgSQL body of the trigger function for a given column.
   *
   * @param string $column The name of the column being updated.
   * @return string The body of the SQL function (NEW.column = CURRENT_TIMESTAMP).
   */
  public function getFunctionBody(string $column): string
  {
    return <<<SQL
              BEGIN
                NEW."$column" = CURRENT_TIMESTAMP;
                RETURN NEW;
              END;
              SQL;
  }

  /**
   * Generates the CREATE TRIGGER SQL statement for the given function and table.
   *
   * @param string $tableName The name of the table.
   * @param string $functionName The name of the function to bind.
   * @return string The full CREATE TRIGGER SQL statement.
   */
  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return <<<SQL
              CREATE TRIGGER trigger_$functionName
              BEFORE UPDATE ON {$tableName}
              FOR EACH ROW
              EXECUTE FUNCTION {$functionName}();
              SQL;
  }
}
