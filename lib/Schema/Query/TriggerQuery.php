<?php

namespace AWSD\Schema\Query;

use AWSD\Schema\Attribute\Trigger;
use AWSD\Schema\Mapper\Orchestrator\TriggerOrchestrator;

final class TriggerQuery extends AbstractQuery
{
  public function __construct(object $entity)
  {
    parent::__construct($entity, [Trigger::class]);
  }

  public function generateSql(): ?string
  {

    if (!array_key_exists(Trigger::class, $this->metadata)) return null;

    $columns = array_filter($this->metadata[Trigger::class], fn($meta) => $meta?->onUpdate ?? false);

    if (empty($columns)) return null;

    return $this->getRequests($columns);
  }

  private function getRequests($columns): string
  {
    $mapper = new TriggerOrchestrator();
    if ($mapper->isNotSupported()) return "";

    $triggerSql = [];
    foreach ($columns as $key => $column) {
      $functionName = $mapper->getFunctionName($this->tableName, $key);
      $body         = $mapper->getTriggerFunctionBody($key);
      $trigger      = $mapper->getTriggerDeclaration($this->tableName, $functionName);
      $triggerSql[] = $this->getQuery($functionName, $body, $trigger);
    }
    return implode("\n\n", $triggerSql);
  }



  private function getQuery(string $functionName, string $body, string $trigger): string
  {
    return <<<SQL
            CREATE OR REPLACE FUNCTION $functionName() RETURNS TRIGGER AS $$
            $body
            $$ LANGUAGE plpgsql;

            $trigger;
            SQL;
  }
}
