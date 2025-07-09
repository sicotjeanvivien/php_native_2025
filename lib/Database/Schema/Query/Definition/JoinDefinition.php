<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\definition;

use AWSD\Database\Schema\Config\OrmConfig;
use AWSD\Database\Schema\Enum\JoinType;
use AWSD\Database\Schema\Enum\SqlDialect;
use InvalidArgumentException;

/**
 * Class JoinDefinition
 *
 * Represents a single SQL JOIN clause in a dialect-aware, validated format.
 * This class captures all structural information about the JOIN, including
 * its type, target table, alias, and condition (`ON ... = ...`), and ensures
 * that it conforms to the capabilities of the configured SQL dialect.
 *
 * It does not generate SQL directly — that responsibility belongs to
 * dialect-specific mappers or orchestrators.
 *
 * ---
 * Example usage:
 * ```php
 * $join = new JoinDefinition(
 *   JoinType::LEFT_JOIN,
 *   'user',
 *   'u',
 *   'post.user_id',
 *   '=',
 *   'u.id'
 * );
 * $join->validate(); // Ensures dialect supports LEFT JOIN, format is correct, etc.
 * ```
 *
 * @package AWSD\Database\Schema\Query\definition
 */
final class JoinDefinition
{
  /**
   * The SQL dialect used for validating join type support.
   *
   * @var SqlDialect
   */
  private readonly SqlDialect $sqlDialect;

  /**
   * @param JoinType $joinType   The type of JOIN (e.g., INNER_JOIN, LEFT_JOIN).
   * @param string   $table      The name of the table to join.
   * @param string   $alias      The alias used in the SQL query for the joined table.
   * @param string   $onLeft     The left-hand side of the join condition (e.g., "post.user_id").
   * @param string   $operator   The operator used to compare join columns (typically "=").
   * @param string   $onRight    The right-hand side of the join condition (e.g., "user.id").
   */
  public function __construct(
    public readonly JoinType $joinType,
    public readonly string $table,
    public readonly string $alias,
    public readonly string $onLeft,
    public readonly string $operator,
    public readonly string $onRight,
  ) {
    $this->sqlDialect = OrmConfig::getInstance()->getDialect();
    $this->validate();
  }

  /**
   * Validates that the join definition is compatible with the current SQL dialect.
   *
   * Performs:
   * - Check that the join type is supported by the dialect
   * - Check that the operator is allowed in JOIN clauses
   * - Check that both column references follow "table.column" format
   *
   * @throws InvalidArgumentException If any of the checks fail.
   */
  public function validate(): void
  {
    $this->validateType();
    $this->validateOperator();
    $this->validateColumnReferences();
  }

  /**
   * Validates that the join type is supported by the current SQL dialect.
   *
   * @throws InvalidArgumentException If the join type is not allowed.
   */
  private function validateType(): void
  {
    $type = strtoupper($this->joinType->name);

    $validTypes = array_map(
      fn(JoinType $j) => $j->name,
      JoinType::supportedForDialect($this->sqlDialect)
    );

    if (!in_array($type, $validTypes, true)) {
      throw new InvalidArgumentException(
        "Join type '{$this->joinType->name}' is not supported by dialect {$this->sqlDialect->name}."
      );
    }
  }

  /**
   * Validates that the operator used in the join is one of the supported forms.
   *
   * Allowed operators: '=', '!=', '<>'
   *
   * @throws InvalidArgumentException If an unsupported operator is used.
   */
  private function validateOperator(): void
  {
    $allowedOperators = ['=', '<>', '!='];

    if (!in_array($this->operator, $allowedOperators, true)) {
      throw new InvalidArgumentException("Invalid operator '{$this->operator}' in JOIN clause.");
    }
  }

  /**
   * Validates that both sides of the join condition use the format "table.column".
   *
   * This ensures clarity and compatibility across dialects that require explicit references.
   *
   * @throws InvalidArgumentException If either reference does not match the expected format.
   */
  private function validateColumnReferences(): void
  {
    foreach ([$this->onLeft, $this->onRight] as $columnRef) {
      if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', $columnRef)) {
        throw new InvalidArgumentException(
          "Invalid column reference format: '{$columnRef}'. Expected format: table.column"
        );
      }
    }
  }
}
