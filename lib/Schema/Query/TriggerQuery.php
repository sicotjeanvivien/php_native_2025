<?php

namespace AWSD\Schema\Query;

use AWSD\Schema\Attribute\Trigger;
use AWSD\Schema\Mapper\Orchestrator\TriggerOrchestrator;

/**
 * Class TriggerQuery
 *
 * Generates SQL trigger declarations for a given entity, based on #[Trigger] attributes.
 *
 * This query builder focuses on PostgreSQL support for triggers. It inspects entity properties
 * annotated with #[Trigger], filters those with `onUpdate: true`, and delegates the construction
 * of function bodies and trigger statements to the TriggerOrchestrator.
 *
 * Only entities using #[Trigger] on their fields will result in SQL output.
 * If the database engine does not support triggers (non-PostgreSQL), an empty string is returned.
 *
 * Example usage:
 *   $query = new TriggerQuery($userEntity);
 *   $sql = $query->generateSql();
 *
 * @see AWSD\Schema\Attribute\Trigger
 * @see AWSD\Schema\Mapper\Orchestrator\TriggerOrchestrator
 */
final class TriggerQuery extends AbstractQuery
{

  /**
   * Constructor
   *
   * Initializes the TriggerQuery with the given entity and filters for #[Trigger] attributes.
   *
   * @param object $entity The entity instance (e.g. new User()).
   */
  public function __construct(string $entity)
  {
    parent::__construct($entity, [Trigger::class]);
  }

  /**
   * Generates the SQL trigger statements for the entity.
   *
   * If no #[Trigger] attributes are found, or no `onUpdate` triggers are defined, returns null.
   * Delegates trigger body and declaration to the TriggerOrchestrator.
   *
   * @return string|null The complete SQL trigger definition or null if no triggers.
   */
  public function generateSql(): ?string
  {

    if (!array_key_exists(Trigger::class, $this->metadata)) return null;

    $columns = array_filter($this->metadata[Trigger::class], fn($meta) => $meta?->onUpdate ?? false);

    if (empty($columns)) return null;

    return $this->getRequests($columns);
  }

  /**
   * Builds the SQL content for all defined triggers.
   *
   * Skips generation if the current SQL dialect does not support triggers.
   *
   * @param array $columns Associative array of field name => Trigger attribute metadata.
   * @return string SQL block defining all triggers and their functions.
   */
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

  /**
   * Composes a full SQL statement for a trigger function and its invocation.
   *
   * @param string $functionName The name of the SQL function to create.
   * @param string $body         The PL/pgSQL function body.
   * @param string $trigger      The SQL trigger declaration.
   * @return string The final SQL snippet.
   */
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
