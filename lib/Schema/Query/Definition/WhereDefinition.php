<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\definition;

use AWSD\Schema\Enum\WhereOperator;

/**
 * Class WhereDefinition
 *
 * Represents a single SQL WHERE condition in normalized and type-safe form.
 * Ensures that the operator and its value are compatible at runtime.
 *
 * Example:
 * ```php
 * $def = new WhereDefinition('email', WhereOperator::LIKE, '%@gmail.com');
 * $def->validate(); // throws if invalid
 * ```
 *
 * @package AWSD\Schema\Query\definition
 */
final class WhereDefinition
{
  /**
   * @param string               $field    The name of the column (e.g. 'email').
   * @param WhereOperator        $operator The SQL operator (enum).
   * @param string|array|null    $value    The associated value(s), depending on operator.
   */
  public function __construct(
    public readonly string $field,
    public readonly WhereOperator $operator,
    public readonly string|array|null $value
  ) {}

  /**
   * Validates the compatibility between the operator and the provided value.
   *
   * @throws \InvalidArgumentException If the value is invalid for the given operator.
   */
  public function validate(): void
  {
    match ($this->operator) {
      WhereOperator::IN,
      WhereOperator::NOT_IN        => $this->assertArrayOfScalars(),

      WhereOperator::BETWEEN       => $this->assertRangeArray(),

      WhereOperator::LIKE,
      WhereOperator::NOT_LIKE      => $this->assertScalarString(),

      WhereOperator::IS_NULL,
      WhereOperator::IS_NOT_NULL   => $this->assertNullValue(),

      default                      => $this->assertScalar(),
    };
  }

  /**
   * Ensures the value is a non-empty array of scalars (for IN, NOT IN).
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
   */
  private function assertScalar(): void
  {
    if (!is_scalar($this->value)) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' requires a scalar value.");
    }
  }

  /**
   * Ensures the value is a string (for LIKE, NOT LIKE).
   */
  private function assertScalarString(): void
  {
    if (!is_string($this->value)) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' requires a string value.");
    }
  }

  /**
   * Ensures the value is null (for IS NULL, IS NOT NULL).
   */
  private function assertNullValue(): void
  {
    if ($this->value !== null) {
      throw new \InvalidArgumentException("Operator '{$this->operator->value}' must not have a value.");
    }
  }
}
