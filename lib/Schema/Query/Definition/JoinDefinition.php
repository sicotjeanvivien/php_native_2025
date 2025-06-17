<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\definition;

use AWSD\Schema\Config\OrmConfig;
use AWSD\Schema\Enum\JoinType;
use AWSD\Schema\Enum\SqlDialect;
use InvalidArgumentException;

/**
 * Class JoinDefinition
 *
 * Represents a SQL JOIN clause definition in a portable, dialect-aware format.
 * This object only stores the structure of the JOIN and performs validation
 * against the SQL dialect's capabilities. It does not generate SQL fragments.
 */
final class JoinDefinition
{
  /**
   * The SQL dialect used for validation purposes.
   */
  private readonly SqlDialect $sqlDialect;

  /**
   * @param string $type     The type of join (e.g., "INNER JOIN", "LEFT JOIN", "JOIN")
   * @param string $table    The target table being joined
   * @param string $alias    The alias to be used for the joined table
   * @param string $onLeft   The left-hand side of the join condition (e.g., "post.user_id")
   * @param string $operator The comparison operator used in the join condition (typically "=")
   * @param string $onRight  The right-hand side of the join condition (e.g., "user.id")
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
  }

  /**
   * Validates the join definition against the current SQL dialect.
   *
   * @throws InvalidArgumentException If the join type, operator, or column references are invalid.
   */
  public function validate(): void
  {
    $this->validateType();
    $this->validateOperator();
    $this->validateColumnReferences();
  }

  /**
   * Validates the join type according to the SQL dialect.
   * Accepts values such as 'JOIN', 'LEFT JOIN', etc., and ensures compatibility.
   *
   * @throws InvalidArgumentException
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
   * Validates the operator used in the JOIN condition.
   * Only supports basic equality operators.
   *
   * @throws InvalidArgumentException
   */
  private function validateOperator(): void
  {
    $allowedOperators = ['=', '<>', '!='];

    if (!in_array($this->operator, $allowedOperators, true)) {
      throw new InvalidArgumentException("Invalid operator '{$this->operator}' in JOIN clause.");
    }
  }

  /**
   * Validates that both sides of the JOIN condition follow the format "table.column".
   *
   * @throws InvalidArgumentException
   */
  private function validateColumnReferences(): void
  {
    foreach ([$this->onLeft, $this->onRight] as $columnRef) {
      if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', $columnRef)) {
        throw new InvalidArgumentException("Invalid column reference format: '{$columnRef}'. Expected format: table.column");
      }
    }
  }
}
