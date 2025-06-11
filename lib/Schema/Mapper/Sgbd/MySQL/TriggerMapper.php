<?php

namespace AWSD\Schema\Mapper\SGBD\MySQL;

use AWSD\Schema\Mapper\SGBD\TriggerMapperInterface;

final class TriggerMapper implements TriggerMapperInterface
{
  public function supportsTriggers(): bool
  {
    return false;
  }

  public function getFunctionName(string $tableName, string $column): string
  {
    return "";
  }

  public function getFunctionBody(string $column): string
  {
    return "";
  }

  public function getTriggerDeclaration(string $tableName, string $functionName): string
  {
    return "";
  }
}
