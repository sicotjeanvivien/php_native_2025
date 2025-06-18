<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Component;

use AWSD\Schema\Enum\WhereOperator;
use AWSD\Schema\Query\definition\WhereDefinition;

/**
 * Class WhereComponent
 *
 * Composes a SQL WHERE clause from a set of validated condition definitions.
 * Each condition is added individually using the `add()` method.
 *
 * Supported condition formats:
 * - Simple equality: ['id' => 42]
 * - Structured condition: ['age' => ['operator' => '>', 'value' => 18]]
 * - IN clause: ['status' => ['operator' => 'IN', 'value' => ['active', 'pending']]]
 * - BETWEEN clause: ['created_at' => ['operator' => 'BETWEEN', 'value' => ['2024-01-01', '2024-12-31']]]
 * - LIKE clause: ['email' => ['operator' => 'LIKE', 'value' => '%@gmail.com']]
 * - IS NULL / IS NOT NULL: ['deleted_at' => ['operator' => 'IS NOT NULL']]
 *
 * All conditions are internally transformed into `WhereDefinition` objects,
 * which are individually validated before use.
 *
 * @package AWSD\Schema\Query\Component
 */
final class WhereComponent extends AbstractQueryComponent
{
  /**
   * @var WhereDefinition[] List of registered WHERE conditions.
   */
  private array $wheres = [];

  /**
   * Adds a new condition to the WHERE clause.
   *
   * @param array $condition A single associative pair representing the field and its condition.
   *
   * Supported formats:
   * - ['id' => 42]
   * - ['email' => ['operator' => 'LIKE', 'value' => '%@gmail.com']]
   * - ['deleted_at' => ['operator' => 'IS NOT NULL']]
   *
   * @return void
   * @throws \InvalidArgumentException If the condition format is invalid.
   * @see WhereDefinition::validate()
   */
  public function add(array $condition): void
  {
    $whereDefinition = $this->createWhereDefinition($condition);
    $whereDefinition->validate();
    $this->wheres[] = $whereDefinition;
  }

  /**
   * Adds multiple WHERE conditions at once.
   *
   * This is a convenience method that wraps multiple calls to `add()`.
   * It accepts a list of field => condition pairs, where each condition
   * can be a scalar value or a structured array with an operator and value.
   * 
   * Example:
   * ```php
   * $where->addMany([
   *   'id' => 5,
   *   'status' => ['operator' => 'IN', 'value' => ['active', 'pending']],
   *   'deleted_at' => ['operator' => 'IS NULL'],
   * ]);
   * ```
   *
   * @param array<string, scalar|array{operator: string, value?: mixed}> $conditions
   *        An associative array of conditions, one per field.
   *
   * @return void
   * @throws \InvalidArgumentException If any condition is invalid.
   * @see self::add()
   */

  public function addMany(array $conditions): void
  {
    foreach ($conditions as $field => $definition) {
      $this->add([$field => $definition]);
    }
  }

  /**
   * Builds and returns the full SQL WHERE clause.
   *
   * Example output:
   *   "WHERE id = :id AND email LIKE :email"
   *
   * @return string The WHERE SQL clause or empty string if no conditions.
   */
  public function getQuery(): string
  {
    if (empty($this->wheres)) return '';

    $clauses = array_map(fn(WhereDefinition $w) => $this->render($w), $this->wheres);
    return 'WHERE ' . implode(' AND ', $clauses);
  }

  /**
   * Converts a raw associative condition into a `WhereDefinition`.
   *
   * @param array $condition One condition in the form ['field' => value] or ['field' => ['operator' => string, 'value' => mixed]]
   * @return WhereDefinition
   * @throws \InvalidArgumentException If the condition format is invalid or the operator is unknown.
   */

  private function createWhereDefinition(array $condition): WhereDefinition
  {
    if (count($condition) !== 1) {
      throw new \InvalidArgumentException("Each WHERE condition must have exactly one key.");
    }

    $field = array_key_first($condition);
    $raw = $condition[$field];

    $operator = '=';
    $value = $raw;

    if (is_array($raw) && isset($raw['operator'])) {
      $operator = strtoupper($raw['operator']);
      $value = $raw['value'] ?? null;
    }

    $whereOperator = WhereOperator::fromString($operator);
    return new WhereDefinition($field, $whereOperator, $value);
  }

  /**
   * Renders a validated `WhereDefinition` into its corresponding SQL fragment.
   *
   * @param WhereDefinition $where The condition to render.
   * @return string SQL expression like "email LIKE :email"
   */
  private function render(WhereDefinition $where): string
  {
    return match ($where->operator) {
      WhereOperator::IN          => $this->buildInCondition($where->field, $where->value),
      WhereOperator::BETWEEN     => $this->buildBetweenCondition($where->field, $where->value),
      WhereOperator::LIKE        => $this->buildLikeCondition($where->field, $where->value),
      WhereOperator::IS_NOT_NULL => $this->buildIsNotNullCondition($where->field),
      WhereOperator::IS_NULL     => $this->getNullCondition($where->field),
      default                    => $this->getDefaultCondition($where->field, $where->value),
    };
  }

  /**
   * Generates SQL fragment for IS NULL.
   *
   * @param string $field The column name.
   * @return string SQL "IS NULL" clause.
   */
  private function getNullCondition(string $field): string
  {
    return "$field IS NULL";
  }

  /**
   * Generates SQL fragment for IS NOT NULL.
   *
   * @param string $field The column name.
   * @return string SQL "IS NOT NULL" clause.
   */
  private function buildIsNotNullCondition(string $field): string
  {
    return "$field IS NOT NULL";
  }

  /**
   * Generates SQL fragment for default scalar comparison.
   *
   * @param string $field The column name.
   * @param mixed $value Scalar value to compare against.
   * @return string SQL fragment with a placeholder (e.g. "id = :id").
   */
  private function getDefaultCondition(string $field, mixed $value): string
  {
    $placeholder = $this->generatePlaceholder($field);
    $this->registerParam($placeholder, $value);
    return "$field = $placeholder";
  }

  /**
   * Generates SQL fragment for IN (...) clause.
   *
   * @param string $field The column name.
   * @param array $values List of scalar values.
   * @return string SQL IN clause with parameterized values.
   */
  private function buildInCondition(string $field, array $values): string
  {
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
   * Generates SQL fragment for BETWEEN a AND b clause.
   *
   * @param string $field The column name.
   * @param array $range Exactly two scalar values [min, max].
   * @return string SQL BETWEEN clause.
   */
  private function buildBetweenCondition(string $field, array $range): string
  {
    [$min, $max] = $range;
    $p1 = ":{$field}_min";
    $p2 = ":{$field}_max";
    $this->registerParam($p1, $min);
    $this->registerParam($p2, $max);
    return "$field BETWEEN $p1 AND $p2";
  }

  /**
   * Generates SQL fragment for LIKE condition.
   *
   * @param string $field The column name.
   * @param string $value Pattern string with wildcards (e.g. "%@gmail.com").
   * @return string SQL LIKE clause with a bound placeholder.
   */
  private function buildLikeCondition(string $field, string $value): string
  {
    $placeholder = $this->generatePlaceholder($field);
    $this->registerParam($placeholder, $value);
    return "$field LIKE $placeholder";
  }
}
