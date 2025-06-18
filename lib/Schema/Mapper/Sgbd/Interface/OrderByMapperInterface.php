<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\interface;

use AWSD\Schema\Query\definition\OrderByDefinition;

/**
 * Interface OrderByMapperInterface
 *
 * Defines the contract for SQL ORDER BY clause builders per SQL dialect (PostgreSQL, MySQL, SQLite).
 *
 * Each implementation must know how to:
 * - Build ORDER BY fragments using direction and nulls
 * - Respect or emulate dialect-specific syntax
 * - Fallback when a feature (e.g. `NULLS FIRST`) is not supported
 *
 * @package AWSD\Schema\Mapper\SGBD
 */
interface OrderByMapperInterface
{
  /**
   * Builds the SQL ORDER BY clause fragments for a given dialect.
   *
   * The returned array may contain multiple expressions (e.g., MySQL emulation),
   * or a single expression (e.g., PostgreSQL native syntax).
   *
   * @param OrderByDefinition $order The validated ORDER BY clause definition.
   * @return array<int, string> List of SQL fragments (e.g., ["IS NULL ASC", "created_at DESC"]).
   */
  public function buildClause(OrderByDefinition $order): array;

  /**
   * Builds the SQL fragment for sort direction.
   *
   * @param string $direction The sorting direction (must be "ASC" or "DESC", case-insensitive).
   * @return string SQL-compatible direction fragment (e.g., "DESC").
   *
   * @throws \RuntimeException If direction is not supported or malformed.
   */
  public function buildDirection(string $direction): string;

  /**
   * Builds the SQL fragment for NULLS FIRST or NULLS LAST.
   *
   * If the dialect supports native null handling (PostgreSQL), it returns it directly.
   * If not supported (SQLite), may return empty string or raise a warning.
   *
   * @param string $nulls The nulls placement keyword ("FIRST" or "LAST").
   * @return string SQL-compatible NULLS fragment (e.g., "NULLS FIRST", "IS NULL ASC", or "").
   *
   * @throws \RuntimeException If the nulls modifier is not valid or unsupported.
   */
  public function buildNulls(string $nulls): string;
}
