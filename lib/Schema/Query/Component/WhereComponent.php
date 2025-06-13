<?php

namespace AWSD\Schema\Query\Component;

use AWSD\Schema\Helper\StringHelper;

/**
 * Class WhereComponent
 *
 * Builds a SQL WHERE clause from an associative array of conditions,
 * supporting standard operators like `=`, `IN`, `BETWEEN`, `LIKE`, and `IS NULL`.
 *
 * Automatically generates bindable parameters for use with PDO and ensures
 * protection against SQL injection via parameterized placeholders.
 *
 * Supports flexible input structure:
 * - Simple equality: ['id' => 5]
 * - Structured conditions:
 *     [
 *       'status' => ['operator' => 'IN', 'value' => ['active', 'pending']],
 *       'age' => ['operator' => '>', 'value' => 18],
 *       'created_at' => ['operator' => 'BETWEEN', 'value' => ['2024-01-01', '2024-12-31']],
 *       'email' => ['operator' => 'LIKE', 'value' => '%@gmail.com'],
 *     ]
 *
 * Throws InvalidArgumentException when conditions are malformed or ambiguous.
 */
final class WhereComponent implements QueryComponentInterface
{
  /**
   * @var array<string, mixed> The parameters to be bound to the query (used by PDO).
   */
  private array $params = [];

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
   * Builds the full WHERE clause and returns SQL + bound parameters.
   *
   * @return array{sql: string, params: array<string, mixed>} An associative array:
   *         - 'sql' : the WHERE clause (e.g., "WHERE id = :id")
   *         - 'params' : the bound parameters for PDO
   */
  public function build(): array
  {
    if (empty($this->conditions)) {
      return ['sql' => '', 'params' => []];
    }

    $clauses = $this->getClauses();
    return [
      'sql' => 'WHERE ' . implode(' AND ', $clauses),
      'params' => $this->params
    ];
  }

  /**
   * Processes the condition array and builds individual SQL expressions.
   *
   * @return array<int, string> The list of SQL condition expressions.
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
   * Registers a parameter and returns its placeholder.
   *
   * @param string $field The base name of the parameter.
   * @param mixed $value The value to bind.
   * @return string The generated placeholder (e.g., :field_2).
   */
  private function setParam(string $field, mixed $value): string
  {
    $placeholder = $this->getPlaceholder($field);
    $this->params[$placeholder] = $value;
    return $placeholder;
  }

  /**
   * Generates a unique placeholder name to avoid collisions.
   *
   * @param string $field The base name.
   * @param int $suffix Optional suffix (used internally for recursion).
   * @return string A unique parameter placeholder (e.g., :email_2).
   */
  private function getPlaceholder(string $field, int $suffix = 0): string
  {
    $placeholder = ':' . $field . ($suffix ? "_$suffix" : '');
    if (array_key_exists($placeholder, $this->params)) {
      return $this->getPlaceholder($field, ++$suffix);
    }
    return $placeholder;
  }

  /**
   * Parses a structured array condition (e.g. IN, BETWEEN, LIKE).
   *
   * @param string $field The field name.
   * @param array $condition Structured condition with 'operator' and 'value'.
   * @return string The SQL fragment.
   */
  private function getArrayCondition(string $field, array $condition): string
  {
    $operator = strtoupper($condition['operator']);
    return match ($operator) {
      'IN'      => $this->buildInCondition($field, $condition['value']),
      'BETWEEN' => $this->buildBetweenCondition($field, $condition['value']),
      'LIKE'    => $this->buildLikeCondition($field, $condition['value']),
      default   => $this->getDefaultArrayCondition($field, $condition)
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
   * Builds a default equality condition for scalar values.
   *
   * @param string $field The field name.
   * @param mixed $value The value to compare.
   * @return string SQL expression with a placeholder.
   */
  private function getDefaultCondition(string $field, mixed $value): string
  {
    $placeholder = $this->setParam($field, $value);
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
      $placeholders[] = $this->setParam($field, $value);
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
    $p1 = $this->setParam($field . '_min', $min);
    $p2 = $this->setParam($field . '_max', $max);
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
    $placeholder = $this->setParam($field, $value);
    return "$field LIKE $placeholder";
  }

  /**
   * Handles other operators (e.g., >, <, !=) in structured conditions.
   *
   * @param string $field The field name.
   * @param array $condition Must contain 'operator' and 'value'.
   * @return string SQL clause.
   */
  private function getDefaultArrayCondition(string $field, array $condition): string
  {
    $operator = strtoupper($condition['operator'] ?? '=');
    $value = $condition['value'] ?? null;
    $placeholder = $this->setParam($field, $value);
    return "$field $operator $placeholder";
  }
}
