<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\definition;

/**
 * Class OrderByDefinition
 *
 * Represents a normalized and validated SQL ORDER BY clause.
 *
 * This class encapsulates:
 * - the target column name (`field`),
 * - the sorting direction (`ASC` or `DESC`),
 * - and the nulls placement (`FIRST` or `LAST`, PostgreSQL only).
 *
 * All inputs are validated at construction, and helper methods are provided
 * to retrieve normalized (uppercase) values suitable for SQL generation.
 *
 * ---
 * Example usage:
 * ```php
 * new OrderByDefinition('name'); // defaults to ASC
 * new OrderByDefinition('created_at', 'DESC', 'LAST');
 * ```
 *
 * @package AWSD\Database\Schema\Query\definition
 */
final class OrderByDefinition
{

  /**
   * @var string[] Valid SQL directions.
   */
  private const VALID_DIRECTIONS = ['ASC', 'DESC'];

  /**
   * @var string[] Valid NULLS ordering modifiers.
   */
  private const VALID_NULLS = ['FIRST', 'LAST'];


  /**
   * @param string      $field     The column name to sort by (e.g., "name").
   * @param string      $direction Sort direction: "ASC" or "DESC" (default = "ASC").
   * @param string|null $nulls     NULLS placement: "FIRST", "LAST", or null (optional).
   *
   * @throws \InvalidArgumentException If any of the values are invalid.
   */
  public function __construct(
    public readonly string $field,
    public readonly string $direction = 'ASC',
    public readonly ?string $nulls = null
  ) {
    $this->validate();
  }

  /**
   * Validates the ORDER BY clause definition.
   *
   * Rules:
   * - `field` must be a valid SQL identifier (e.g., `created_at`, not `created-at`)
   * - `direction` must be "ASC" or "DESC" (case-insensitive)
   * - if present, `nulls` must be "FIRST" or "LAST" (case-insensitive)
   *
   * @throws \InvalidArgumentException
   */
  public function validate(): void
  {
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->field)) {
      throw new \InvalidArgumentException("Invalid field name in ORDER BY: '{$this->field}'");
    }

    if (!in_array(strtoupper($this->direction), self::VALID_DIRECTIONS, true)) {
      throw new \InvalidArgumentException("Invalid ORDER BY direction: '{$this->direction}'");
    }

    if ($this->nulls !== null && !in_array(strtoupper($this->nulls), self::VALID_NULLS, true)) {
      throw new \InvalidArgumentException("Invalid NULLS clause in ORDER BY: '{$this->nulls}'");
    }
  }

  /**
   * Returns the normalized direction in uppercase (ASC or DESC).
   *
   * @return string The SQL-compliant direction keyword.
   */
  public function getDirection(): string
  {
    return strtoupper($this->direction);
  }

  /**
   * Returns the normalized NULLS placement keyword (FIRST or LAST), or null.
   *
   * @return string|null The NULLS modifier or null if not specified.
   */
  public function getNulls(): ?string
  {
    return $this->nulls !== null ? strtoupper($this->nulls) : null;
  }
}
