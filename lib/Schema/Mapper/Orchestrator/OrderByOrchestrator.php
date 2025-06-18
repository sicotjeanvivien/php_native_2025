<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\Orchestrator;

use AWSD\Schema\Mapper\SGBD\MySQL\OrderByMapper as MySQLOrderByMapper;
use AWSD\Schema\Mapper\SGBD\PostgreSQL\OrderByMapper as PostgreSQLOrderByMapper;
use AWSD\Schema\Mapper\SGBD\SQLite\OrderByMapper as SQLiteOrderByMapper;
use AWSD\Schema\Query\definition\OrderByDefinition;

/**
 * Class OrderByOrchestrator
 *
 * Delegates the construction of an ORDER BY clause fragment to the appropriate
 * SQL dialect-specific mapper (PostgreSQL, MySQL, SQLite).
 *
 * This orchestrator receives a fully validated `OrderByDefinition` object and
 * extracts its normalized `direction` and `nulls` placement.
 * It then delegates the formatting to the appropriate `OrderByMapper` for the current SGBD.
 *
 * ---
 * Supported features:
 * - PostgreSQL: full support for `NULLS FIRST` / `NULLS LAST`
 * - MySQL & SQLite: fallback emulation or omission of null handling
 *
 * ---
 * Example usage:
 * ```php
 * $order = new OrderByDefinition('created_at', 'DESC', 'LAST');
 * $sql = OrderByOrchestrator::format($order); // ["DESC NULLS LAST"]
 * ```
 *
 * @package AWSD\Schema\Mapper\Orchestrator
 */
final class OrderByOrchestrator extends AbstractOrchestrator
{
  /**
   * The ORDER BY clause definition (field, direction, nulls).
   *
   * @var OrderByDefinition
   */
  private OrderByDefinition $condition;

  /**
   * The normalized direction (ASC or DESC).
   *
   * @var string
   */
  private string $direction;

  /**
   * The normalized NULLS placement ("FIRST", "LAST", or null).
   *
   * @var string|null
   */
  private ?string $nulls;

  /**
   * Constructor
   *
   * Instantiates the correct `OrderByMapper` based on the configured SQL dialect,
   * and extracts normalized direction and nulls info from the given condition.
   *
   * @param OrderByDefinition $condition A validated ORDER BY clause input.
   */
  public function __construct(OrderByDefinition $condition)
  {
    parent::__construct();

    $this->sgbdMapper = $this->getSgbdImplementation([
      'pgsql'  => new PostgreSQLOrderByMapper(),
      'sqlite' => new SQLiteOrderByMapper(),
      'mysql'  => new MySQLOrderByMapper(),
    ]);

    $this->condition = $condition;
    $this->resolveCondition();
  }

  /**
   * Static factory to build SQL clause fragments from an `OrderByDefinition`.
   *
   * @param OrderByDefinition $order The ORDER BY clause definition.
   * @return array<int, string> One or more SQL clause fragments (e.g., ["DESC NULLS LAST"])
   */
  public static function format(OrderByDefinition $order): array
  {
    $instance = new self($order);
    return $instance->sgbdMapper->buildClause($order);
  }

  /**
   * Returns the full SQL clause as a string for a single field.
   *
   * @return string SQL expression like "ASC NULLS FIRST".
   */
  public function getSqlOrder(): string
  {
    $sql = $this->sgbdMapper->buildDirection($this->direction);
    if (!empty($this->nulls)) {
      $sql .= ' ' . $this->sgbdMapper->buildNulls($this->nulls);
    }
    return $sql;
  }

  /**
   * Extracts normalized direction and nulls placement from the definition.
   *
   * Uses helper methods from `OrderByDefinition` for uppercase normalization.
   *
   * @return void
   */
  private function resolveCondition(): void
  {
    $this->direction = $this->condition->getDirection();
    $this->nulls = $this->condition->getNulls();
  }
}
