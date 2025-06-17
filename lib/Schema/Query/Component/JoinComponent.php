<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Component;

use AWSD\Schema\Enum\JoinType;
use AWSD\Schema\Query\definition\JoinDefinition;
use InvalidArgumentException;

/**
 * Class JoinComponent
 *
 * Manages JOIN clauses in a SQL SELECT query.
 * Allows adding JOIN definitions from a simplified array syntax,
 * with automatic alias generation and dialect-aware validation.
 */
final class JoinComponent extends AbstractQueryComponent
{
  /**
   * @var JoinDefinition[] List of join definitions.
   */
  private array $joins = [];

  /**
   * @var array<string, int> Tracks alias counts per table name to ensure uniqueness.
   */
  private array $aliasCounts = [];

  /**
   * Adds a JOIN clause using a simplified array definition.
   * The method validates the array structure, initializes a JoinDefinition,
   * performs dialect-aware validation, and stores the result.
   *
   * @param array{
   *     type: string,
   *     table: string,
   *     on: array{0: string, 1: string, 2: string},
   *     alias?: string
   * } $definition The JOIN definition array.
   *
   * @throws InvalidArgumentException If required keys are missing or invalid.
   */
  public function add(array $definition): void
  {
    $this->validateDefinition($definition);
    $join = $this->createJoinDefinition($definition);
    $join->validate();
    $this->joins[] = $join;
  }

  /**
   * Returns the SQL fragment containing all JOIN clauses.
   *
   * @return string A space-separated SQL fragment containing all JOIN clauses.
   */
  public function getQuery(): string
  {
    $sqlParts = [];
    foreach ($this->joins as $join) {
      $sqlParts[] = $this->buildJoin($join);
    }
    return implode(' ', $sqlParts);
  }

  /**
   * Creates a JoinDefinition from a simplified associative array.
   * This method does not perform validation — it simply maps the array to an object.
   *
   * @param array{
   *     type: string,
   *     table: string,
   *     on: array{0: string, 1: string, 2: string},
   *     alias?: string
   * } $definition
   *
   * @return JoinDefinition
   */
  private function createJoinDefinition(array $definition): JoinDefinition
  {
    [$onLeft, $operator, $onRight] = $definition['on'];
    $alias = $definition['alias'] ?? $this->generateAlias($definition['table']);

    return new JoinDefinition(
      joinType: JoinType::fromString($definition['type']),
      table: $definition['table'],
      alias: $alias,
      onLeft: $onLeft,
      operator: $operator,
      onRight: $onRight
    );
  }

  /**
   * Generates a unique alias for a given table name.
   * Example: "user" → "user_1", "user_2", etc.
   *
   * @param string $table The table name for which to generate an alias.
   * @return string A unique alias.
   */
  private function generateAlias(string $table): string
  {
    $index = ($this->aliasCounts[$table] ?? 0) + 1;
    $this->aliasCounts[$table] = $index;
    return "{$table}_{$index}";
  }

  /**
   * Validates that the required keys are present in the join definition array.
   *
   * @param array $definition The simplified join array.
   * @throws InvalidArgumentException If 'type', 'table', or 'on' is missing.
   */
  private function validateDefinition(array $definition): void
  {
    if (!isset($definition['type'], $definition['table'], $definition['on'])) {
      throw new InvalidArgumentException("Missing required join keys: 'type', 'table', 'on'");
    }
  }

  /**
   * Builds the SQL string for a single JOIN clause.
   * Dispatches to the appropriate internal builder depending on the join type.
   *
   * @param JoinDefinition $join
   * @return string The rendered JOIN clause
   *
   * @throws \RuntimeException If the join type is unsupported
   */
  private function buildJoin(JoinDefinition $join): string
  {
    return match (true) {
      in_array($join->joinType, JoinType::generic(), true)  => $this->buildGenericJoin($join),
      default                                               => throw new \RuntimeException("Unhandled join type: {$join->joinType->value}")
    };
  }

  /**
   * Builds a generic SQL JOIN clause (compatible across PostgreSQL, MySQL, SQLite),
   * and applies alias replacement in ON condition.
   *
   * @param JoinDefinition $join
   * @return string The complete JOIN clause with alias-corrected ON condition
   */
  private function buildGenericJoin(JoinDefinition $join): string
  {
    $onLeft  = $this->replaceAlias($join->onLeft, $join->table, $join->alias);
    $onRight = $this->replaceAlias($join->onRight, $join->table, $join->alias);

    return "{$join->joinType->value} {$join->table} AS {$join->alias} ON $onLeft {$join->operator} $onRight";
  }

  /**
   * Replaces the table name in a column reference (e.g. "user.id") with its alias if it matches.
   *
   * @param string $expression The original column reference (e.g. "user.id")
   * @param string $originalTable The expected table name to replace
   * @param string $alias The alias to apply
   * @return string The column reference with alias applied (e.g. "user_1.id")
   */
  private function replaceAlias(string $expression, string $originalTable, string $alias): string
  {
    return preg_replace('/^' . preg_quote($originalTable, '/') . '\./', "{$alias}.", $expression);
  }
}
