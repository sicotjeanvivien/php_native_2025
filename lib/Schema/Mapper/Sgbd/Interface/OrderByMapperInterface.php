<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\interface;

/**
 * Interface OrderByMapperInterface
 *
 * Defines the contract for SQL ORDER BY clause builders per SQL dialect (PostgreSQL, MySQL, SQLite).
 * Each implementation is responsible for building the ORDER BY fragment according to its engine's syntax.
 *
 * @package AWSD\Schema\Mapper\SGBD
 */
interface OrderByMapperInterface
{

  public function buildClause(string $direction, ?string $nulls = null): array;

  /**
   * Builds the direction clause for ORDER BY (ASC or DESC).
   *
   * @param string $direction The sorting direction (must be "ASC" or "DESC").
   * @return string The SQL-compatible ORDER BY direction fragment.
   */
  public function buildDirection(string $direction): string;

  /**
   * Builds the NULLS FIRST/LAST clause (if supported by the dialect).
   *
   * @param string $nulls Either "FIRST" or "LAST".
   * @return string The SQL-compatible NULLS ordering fragment.
   */
  public function buildNulls(string $nulls): string;
}
