<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Component;

use AWSD\Schema\Mapper\Orchestrator\OrderByOrchestrator;

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
 * @package AWSD\Schema\Query\Component
 */
final class OrderByComponent extends AbstractQueryComponent
{
  /**
   * @var array<string, string|array{direction?: string, nulls?: string}>
   *      Associative array of order instructions. Keys are column names.
   */
  private array $orders;

  /**
   * Constructor
   *
   * @param array<string, string|array{direction?: string, nulls?: string}> $orders
   *        An associative array describing ORDER BY rules.
   */
  public function __construct(array $orders)
  {
    $this->orders = $orders;
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

    foreach ($this->orders as $field => $order) {
      foreach (OrderByOrchestrator::format($order) as $fragment) {
        $clauses[] = "$field $fragment";
      }
    }

    return $clauses;
  }
}
