<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\definition;

use AWSD\Schema\Enum\WhereOperator;

/**
 * Class WhereDefinition
 *
 * Represents a normalized and strictly typed SQL WHERE condition.
 * This class encapsulates the target field, the SQL operator (as enum),
 * and the corresponding value(s), with built-in validation logic.
 *
 * 🔐 Ensures SQL-type safety and semantic correctness for each operator.
 * 🔄 Delegates semantic rules (scalar, array, null) to the enum.
 *
 * ---
 * Examples:
 * ```php
 * new WhereDefinition('email', WhereOperator::LIKE, '%@gmail.com');
 * new WhereDefinition('id', WhereOperator::IN, [1, 2, 3]);
 * new WhereDefinition('deleted_at', WhereOperator::IS_NULL, null);
 * ```
 *
 * @package AWSD\Schema\Query\definition
 */
final class WhereDefinition
{
  /**
   * @param string                   $field    The name of the database column (e.g. 'id', 'email').
   * @param WhereOperator            $operator The SQL operator as enum (e.g. EQUAL, IN, IS_NULL).
   * @param int|string|array|null    $value    The associated value or values. Must match the operator requirements.
   */
  public function __construct(
    public readonly string $field,
    public readonly WhereOperator $operator,
    public readonly int|string|array|null $value
  ) {
    $this->validate();
  }

  /**
   * Validates that the value matches the semantic rules for the given operator.
   *
   * Rules enforced:
   * - IS NULL / IS NOT NULL: value must be null
   * - IN / NOT IN: non-empty array of scalar values
   * - BETWEEN: array with exactly two scalar values
   * - LIKE / NOT LIKE: scalar string
   * - Others: scalar (int, float, string, bool)
   *
   * @throws \InvalidArgumentException If the value is invalid for the operator.
   */
  public function validate(): void
  {
    match (true) {
      $this->operator->requiresArray()      => $this->assertArrayOfScalars(),
      $this->operator->isBetween()          => $this->assertRangeArray(),
      $this->operator->requiresString()     => $this->assertScalarString(),
      $this->operator->isUnary()            => $this->assertNullValue(),
      default                               => $this->assertScalar(),
    };
  }

  /**
   * Ensures the value is a non-empty array of scalars (for IN, NOT IN).
   *
   * @throws \InvalidArgumentException
   */
  private function assertArrayOfScalars(): void
  {
    if (!is_array($this->value) || $this->value === []) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' requires a non-empty array of scalar values.");
    }

    foreach ($this->value as $v) {
      if (!is_scalar($v)) {
        throw new \InvalidArgumentException("Operator '{$this->operator->value}' requires only scalar values.");
      }
    }
  }

  /**
   * Ensures the value is an array with exactly two scalar elements (for BETWEEN).
   *
   * @throws \InvalidArgumentException
   */
  private function assertRangeArray(): void
  {
    if (!is_array($this->value) || count($this->value) !== 2) {
      throw new \InvalidArgumentException("BETWEEN operator requires an array with exactly two elements.");
    }

    [$min, $max] = $this->value;

    if (!is_scalar($min) || !is_scalar($max)) {
      throw new \InvalidArgumentException("BETWEEN bounds must be scalar values.");
    }
  }

  /**
   * Ensures the value is a scalar (for =, >, <, etc.).
   *
   * @throws \InvalidArgumentException
   */
  private function assertScalar(): void
  {
    if (!is_scalar($this->value)) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' requires a scalar value.");
    }
  }

  /**
   * Ensures the value is a string (for LIKE, NOT LIKE).
   *
   * @throws \InvalidArgumentException
   */
  private function assertScalarString(): void
  {
    if (!is_string($this->value)) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' requires a string value.");
    }
  }

  /**
   * Ensures the value is null (for IS NULL, IS NOT NULL).
   *
   * @throws \InvalidArgumentException
   */
  private function assertNullValue(): void
  {
    if ($this->value !== null) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' must not have a value.");
    }
  }
}
