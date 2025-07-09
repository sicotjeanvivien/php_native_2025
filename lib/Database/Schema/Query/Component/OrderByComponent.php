<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\Component;

use AWSD\Database\Schema\Mapper\Orchestrator\OrderByOrchestrator;
use AWSD\Database\Schema\Query\definition\OrderByDefinition;

/**
 * Class OrderByComponent
 *
 * Builds the SQL ORDER BY clause from an associative array of field-order instructions.
 * Delegates dialect-specific formatting (direction, nulls handling, fallback) to the OrderByOrchestrator.
 *
 * Example input:
 * [
 *   'name' => 'ASC',
 *   'created_at' => ['direction' => 'DESC', 'nulls' => 'LAST']
 * ]
 *
 * PostgreSQL output:
 * ORDER BY name ASC, created_at DESC NULLS LAST
 *
 * MySQL output:
 * ORDER BY name ASC, created_at IS NULL ASC, created_at DESC
 *
 * @package AWSD\Database\Schema\Query\Component
 */
final class OrderByComponent extends AbstractQueryComponent
{

  /** @var OrderByDefinition[] */
  private array $orders = [];

  public function add(string $field, string|array|null $definition = null): void
  {
    $direction = 'ASC';
    $nulls = null;

    if (is_string($definition)) {
      $direction = $definition;
    } elseif (is_array($definition)) {
      $direction = $definition['direction'] ?? 'ASC';
      $nulls = $definition['nulls'] ?? null;
    }

    $this->orders[] = new OrderByDefinition($field, $direction, $nulls);
  }

  /**
   * Adds multiple ORDER BY clauses in batch.
   *
   * Accepts an associative array where keys are field names and values are either:
   * - a string: the direction (e.g., 'ASC' or 'DESC')
   * - an array: with keys 'direction' and/or 'nulls'
   *
   * Examples:
   * ```php
   * $component->addMany([
   *   'name' => 'ASC',
   *   'created_at' => ['direction' => 'DESC', 'nulls' => 'LAST']
   * ]);
   * ```
   *
   * @param array<string, string|array{direction?: string, nulls?: string}> $orders
   * @return void
   */
  public function addMany(array $orders): void
  {
    foreach ($orders as $field => $definition) {
      $this->add($field, $definition);
    }
  }


  /**
   * Generates the full SQL ORDER BY clause.
   *
   * @return string SQL fragment like: "ORDER BY name ASC, created_at DESC NULLS LAST"
   */
  public function getQuery(): string
  {
    if (empty($this->orders)) {
      return '';
    }

    $orderFragments = $this->getClauses();
    $sql = implode(', ', $orderFragments);

    return "ORDER BY $sql";
  }

  /**
   * Builds the individual ORDER BY expressions per field, including dialect-specific clauses.
   *
   * @return array<int, string> List of SQL fragments (e.g. ["name ASC", "created_at DESC NULLS LAST"])
   */
  private function getClauses(): array
  {
    $clauses = [];

    foreach ($this->orders as $orderDef) {
      foreach (OrderByOrchestrator::format($orderDef) as $fragment) {
        $quoted = $this->quote->quoteIdentifier($orderDef->field);
        $clauses[] = "$quoted $fragment";
      }
    }

    return $clauses;
  }
}
