<?php

namespace AWSD\SqlEntity\Query;

class CreateQuery extends AbstractQuery
{

  public function generateSql(): string
  {
    $columns = $this->getSqlColumns();

    $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->tableName . " (\n";
    $lines = [];

    foreach ($columns as $name => $definition) {
      $lines[] = "  $name $definition";
    }

    $sql .= implode(",\n", $lines) . "\n);";

    return $sql;
  }
}
