<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\PostgreSQL;

use AWSD\Schema\Mapper\SGBD\AbstractOrderByMapper;
use AWSD\Schema\Mapper\SGBD\interface\OrderByMapperInterface;

/**
 * Class OrderByMapper (PostgreSQL)
 *
 * Builds SQL ORDER BY fragments compatible with PostgreSQL syntax,
 * including native support for NULLS FIRST / NULLS LAST.
 *
 * @package AWSD\Schema\Mapper\SGBD\PostgreSQL
 */
final class OrderByMapper extends AbstractOrderByMapper implements OrderByMapperInterface
{
  /**
   * Builds the ORDER BY clause fragments for PostgreSQL.
   *
   * @param string $direction Either "ASC" or "DESC".
   * @param string|null $nulls Either "FIRST", "LAST", or null.
   * @return array<int, string> One-element array like ["DESC NULLS LAST"]
   * @throws \RuntimeException If direction or nulls value is invalid.
   */
  public function buildClause(string $direction, ?string $nulls = null): array
  {
    $sql = $this->buildDirection($direction);

    if ($nulls !== null) {
      $sql .= $this->buildNulls($nulls);
    }

    return [$sql];
  }

  /**
   * Returns the PostgreSQL-specific NULLS ordering clause.
   *
   * @param string $nulls Either "FIRST" or "LAST".
   * @return string The SQL NULLS modifier.
   * @throws \RuntimeException If the NULLS modifier is invalid.
   */
  public function buildNulls(string $nulls): string
  {
    if ($this->isNullsInvalid($nulls)) {
      throw new \RuntimeException("Invalid NULLS ordering keyword: '$nulls'");
    }

    return ' NULLS ' . $nulls;
  }
}
