<?php

declare(strict_types=1);

namespace AWSD\Schema\Enum;

/**
 * Enum WhereOperator
 *
 * Defines the list of supported SQL operators used in WHERE clauses.
 * This enum provides type safety and semantic clarity for conditions
 * like equality, comparison, nullability, ranges, and set membership.
 *
 * Common usage includes:
 * - Comparisons: `=`, `!=`, `<>`, `<`, `<=`, `>`, `>=`
 * - Pattern matching: `LIKE`, `NOT LIKE`
 * - Null checks: `IS NULL`, `IS NOT NULL`
 * - Range matching: `BETWEEN`
 * - Set matching: `IN`, `NOT IN`
 *
 * @example
 * ```php
 * $operator = WhereOperator::fromString('LIKE');
 * echo $operator->value; // Outputs 'LIKE'
 * ```
 *
 * @package AWSD\Schema\Enum
 */
enum WhereOperator: string
{
  case EQUAL            = '=';
  case NOT_EQUAL        = '!=';
  case NOT_EQUAL_ALT    = '<>';
  case LESS_THAN        = '<';
  case LESS_THAN_EQUAL  = '<=';
  case GREATER_THAN     = '>';
  case GREATER_EQUAL    = '>=';
  case IN               = 'IN';
  case NOT_IN           = 'NOT IN';
  case BETWEEN          = 'BETWEEN';
  case LIKE             = 'LIKE';
  case NOT_LIKE         = 'NOT LIKE';
  case IS_NULL          = 'IS NULL';
  case IS_NOT_NULL      = 'IS NOT NULL';

  /**
   * Returns the corresponding enum case for a given string operator.
   *
   * Input is normalized with `trim()` and `strtoupper()` to allow flexible matching.
   *
   * @param string $raw The raw operator as string (e.g. '=', 'like', 'IS NOT NULL').
   * @return self The corresponding WhereOperator enum case.
   *
   * @throws \InvalidArgumentException If the operator string is not supported.
   */
  public static function fromString(string $raw): self
  {
    $normalized = strtoupper(trim($raw));

    foreach (self::cases() as $case) {
      if ($case->value === $normalized) {
        return $case;
      }
    }

    throw new \InvalidArgumentException("Unsupported SQL operator: '$raw'.");
  }
}
