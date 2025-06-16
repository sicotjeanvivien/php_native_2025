<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Component;

/**
 * Class WhereComponent
 *
 * Builds a SQL WHERE clause from an associative array of conditions.
 * Supports standard SQL operators including: `=`, `IN`, `BETWEEN`, `LIKE`, `IS NULL`, `IS NOT NULL`, and others.
 *
 * Generates bindable parameters for use with PDO and ensures protection
 * against SQL injection by using parameterized placeholders.
 *
 * Supported input formats:
 * - Simple equality: ['id' => 5]
 * - Structured conditions:
 *     [
 *       'status' => ['operator' => 'IN', 'value' => ['active', 'pending']],
 *       'age' => ['operator' => '>', 'value' => 18],
 *       'created_at' => ['operator' => 'BETWEEN', 'value' => ['2024-01-01', '2024-12-31']],
 *       'email' => ['operator' => 'LIKE', 'value' => '%@gmail.com'],
 *       'deleted_at' => ['operator' => 'IS NOT NULL'],
 *     ]
 *
 * @package AWSD\Schema\Query\Component
 */
final class WhereComponent extends AbstractQueryComponent
{

  /**
   * @var array<string, mixed> The raw array of conditions provided to the constructor.
   */
  private array $conditions;

  /**
   * Constructor
   *
   * @param array<string, mixed> $conditions Associative array of WHERE conditions.
   */
  public function __construct(array $conditions)
  {
    $this->conditions = $conditions;
  }

  /**
   * Builds and returns the WHERE clause as a SQL fragment.
   *
   * @return string The SQL WHERE clause (e.g. "WHERE id = :id AND status IN (...)").
   */
  public function getQuery(): string
  {
    if (empty($this->conditions)) return '';
    $clauses = $this->getClauses();
    $where = implode(' AND ', $clauses);

    return <<<SQL
            WHERE $where
            SQL;
  }

  /**
   * Parses each condition and generates the SQL clause parts.
   *
   * @return array<int, string> List of SQL expressions.
   * @throws \InvalidArgumentException If a structured condition is invalid.
   */
  private function getClauses(): array
  {
    $clauses = [];
    foreach ($this->conditions as $field => $condition) {
      if (is_array($condition) && (!isset($condition['operator']) || !array_key_exists('value', $condition))) {
        throw new \InvalidArgumentException("Condition array must contain 'operator' and 'value' keys.");
      }

      $clauses[] = match (true) {
        is_array($condition)  => $this->getArrayCondition($field, $condition),
        is_null($condition)   => $this->getNullCondition($field),
        default               => $this->getDefaultCondition($field, $condition)
      };
    }
    return $clauses;
  }

  /**
   * Parses a structured array condition (e.g. IN, BETWEEN, LIKE, IS NOT NULL).
   *
   * @param string $field The field name.
   * @param array $condition Structured condition with 'operator' and optionally 'value'.
   * @return string The SQL fragment.
   */
  private function getArrayCondition(string $field, array $condition): string
  {
    $operator = strtoupper($condition['operator']);

    return match ($operator) {
      'IN'         => $this->buildInCondition($field, $condition['value']),
      'BETWEEN'    => $this->buildBetweenCondition($field, $condition['value']),
      'LIKE'       => $this->buildLikeCondition($field, $condition['value']),
      'IS NOT NULL' => $this->buildIsNotNullCondition($field),
      default      => $this->getDefaultArrayCondition($field, $condition)
    };
  }

  /**
   * Builds a SQL fragment for IS NULL condition.
   *
   * @param string $field The field name.
   * @return string SQL expression like "field IS NULL".
   */
  private function getNullCondition(string $field): string
  {
    return "$field IS NULL";
  }

  /**
   * Builds a SQL fragment for IS NOT NULL condition.
   *
   * @param string $field The field name.
   * @return string SQL expression like "field IS NOT NULL".
   */
  private function buildIsNotNullCondition(string $field): string
  {
    return "$field IS NOT NULL";
  }

  /**
   * Builds a default equality condition for scalar values.
   *
   * @param string $field The field name.
   * @param mixed $value The value to compare.
   * @return string SQL expression with a placeholder.
   */
  private function getDefaultCondition(string $field, mixed $value): string
  {
    $placeholder = $this->generatePlaceholder($field);
    $this->registerParam($placeholder, $value);
    return "$field = $placeholder";
  }

  /**
   * Builds an IN (...) clause with bound parameters.
   *
   * @param string $field The field name.
   * @param array $values The list of values.
   * @return string SQL IN clause.
   * @throws \InvalidArgumentException If the values array is empty.
   */
  private function buildInCondition(string $field, array $values): string
  {
    if (empty($values)) {
      throw new \InvalidArgumentException("IN operator requires a non-empty array of values.");
    }
    $placeholders = [];
    foreach ($values as $value) {
      $placeholder = $this->generatePlaceholder($field);
      $this->registerParam($placeholder, $value);
      $placeholders[] = $placeholder;
    }
    $inList = implode(', ', $placeholders);
    return "$field IN ($inList)";
  }

  /**
   * Builds a BETWEEN a AND b clause with two parameters.
   *
   * @param string $field The field name.
   * @param array $range Array with exactly two elements [min, max].
   * @return string SQL BETWEEN clause.
   * @throws \InvalidArgumentException If range does not contain exactly two elements.
   */
  private function buildBetweenCondition(string $field, array $range): string
  {
    if (count($range) !== 2) {
      throw new \InvalidArgumentException("BETWEEN requires exactly two values.");
    }
    [$min, $max] = $range;
    $p1 = ":{$field}_min";
    $p2 = ":{$field}_max";
    $this->registerParam($p1, $min);
    $this->registerParam($p2, $max);
    return "$field BETWEEN $p1 AND $p2";
  }

  /**
   * Builds a LIKE clause with a single parameter.
   *
   * @param string $field The field name.
   * @param string $value The value to search for.
   * @return string SQL LIKE clause.
   * @throws \InvalidArgumentException If the value is not a scalar.
   */
  private function buildLikeCondition(string $field, string $value): string
  {
    if (!is_scalar($value)) {
      throw new \InvalidArgumentException("LIKE operator requires a scalar value.");
    }
    $placeholder = $this->generatePlaceholder($field);
    $this->registerParam($placeholder, $value);
    return "$field LIKE $placeholder";
  }

  /**
   * Handles custom operators (e.g., >, <, !=) in structured conditions.
   *
   * @param string $field The field name.
   * @param array $condition Must contain 'operator' and 'value'.
   * @return string SQL clause.
   */
  private function getDefaultArrayCondition(string $field, array $condition): string
  {
    $operator = strtoupper($condition['operator'] ?? '=');
    $value = $condition['value'] ?? null;
    $placeholder = $this->generatePlaceholder($field);
    $this->registerParam($placeholder, $value);
    return "$field $operator $placeholder";
  }
}
