<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\Orchestrator;

use AWSD\Schema\Mapper\SGBD\MySQL\OrderByMapper as MySQLOrderByMapper;
use AWSD\Schema\Mapper\SGBD\PostgreSQL\OrderByMapper as PostgreSQLOrderByMapper;
use AWSD\Schema\Mapper\SGBD\SQLite\OrderByMapper as SQLiteOrderByMapper;

/**
 * Class OrderByOrchestrator
 *
 * Delegates ORDER BY clause formatting to the appropriate SQL dialect mapper.
 * Supports advanced PostgreSQL features such as NULLS FIRST/LAST, and provides fallback
 * for MySQL and SQLite where native support is absent or implicit.
 *
 * Input can be:
 * - A string direction: "ASC" or "DESC"
 * - A structured array: ['direction' => 'DESC', 'nulls' => 'LAST']
 *
 * Example usage:
 *   OrderByOrchestrator::format('ASC')                       => "ASC"
 *   OrderByOrchestrator::format(['direction' => 'DESC'])     => "DESC"
 *   OrderByOrchestrator::format(['direction' => 'ASC', 'nulls' => 'FIRST']) => "ASC NULLS FIRST"
 *
 * @package AWSD\Schema\Mapper\Orchestrator
 */
final class OrderByOrchestrator extends AbstractOrchestrator
{
  /**
   * @var string|array<string, string|null> The original ORDER BY condition input.
   */
  private string|array $condition;

  /**
   * @var string The normalized direction (ASC or DESC).
   */
  private string $direction;

  /**
   * @var string|null Optional NULLS placement (FIRST or LAST).
   */
  private ?string $nulls;

  /**
   * Constructor
   *
   * Initializes the orchestrator with the correct OrderByMapper
   * based on the current database driver (`$_ENV['DB_DRIVER']`).
   *
   * @param string|array<string, string|null> $condition
   */
  public function __construct(string|array $condition)
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
   * Static factory method to directly generate the SQL ORDER clause.
   *
   * @param string|array<string, string|null> $order The input order condition.
   * @return array SQL fragment like: "DESC NULLS LAST"
   */
  public static function format(string|array $order): array
  {
    $instance = new self($order);
    return $instance->sgbdMapper->buildClause(
      $instance->direction,
      $instance->nulls
    );
  }

  /**
   * Returns the full SQL clause formatted for the current dialect.
   *
   * @return string The ORDER clause for the current field (e.g. "ASC NULLS FIRST").
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
   * Parses the input and normalizes direction and nulls options.
   *
   * @throws \RuntimeException If the input type is unsupported.
   */
  private function resolveCondition(): void
  {
    match (true) {
      is_array($this->condition)  => $this->buildWhenArray($this->condition),
      is_string($this->condition) => $this->buildWhenString($this->condition),
      default                     => throw new \RuntimeException("Invalid ORDER BY condition type: " . gettype($this->condition))
    };
  }

  /**
   * Normalizes array-style input into direction and nulls.
   *
   * @param array<string, string|null> $condition
   */
  private function buildWhenArray(array $condition): void
  {
    $this->direction = strtoupper($condition['direction'] ?? 'ASC');
    $this->nulls = strtoupper($condition['nulls']) ?? null;
  }

  /**
   * Normalizes a string-style direction input.
   *
   * @param string $condition
   */
  private function buildWhenString(string $condition): void
  {
    $this->direction = strtoupper($condition);
    $this->nulls = null;
  }
}
