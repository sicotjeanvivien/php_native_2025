<?php

namespace AWSD\Schema\Mapper\SGBD\PostgreSQL;

use AWSD\Schema\Mapper\SGBD\TriggerMapperInterface;

final class TriggerMapper implements TriggerMapperInterface
{
  public function supportsTriggers(): bool
  {
    return true;
  }

  public function getFunctionName(string $tableName, string $column): string
  {
    return "set_{$tableName}_{$column}";
  }

  public function getFunctionBody(string $column): string
  {
    return <<<SQL
              BEGIN
                NEW."$column" = CURRENT_TIMESTAMP;
                RETURN NEW;
              END;
              SQL;
  }

  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return <<<SQL
              CREATE TRIGGER trigger_{$tableName}_updatedAt
              BEFORE UPDATE ON {$tableName}
              FOR EACH ROW
              EXECUTE FUNCTION {$functionName}();
              SQL;
  }
}
