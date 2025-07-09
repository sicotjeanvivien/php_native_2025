<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\SGBD\PostgreSQL;

use AWSD\Database\Schema\Mapper\SGBD\AbstractOrderByMapper;
use AWSD\Database\Schema\Mapper\SGBD\interface\OrderByMapperInterface;
use AWSD\Database\Schema\Query\definition\OrderByDefinition;

/**
 * Class OrderByMapper (PostgreSQL)
 *
 * Formats ORDER BY clause fragments using PostgreSQL’s native syntax.
 * PostgreSQL supports advanced ordering behavior with `NULLS FIRST` and `NULLS LAST`,
 * which this mapper handles natively and cleanly.
 *
 * ---
 * Example output:
 * ```sql
 * ORDER BY created_at DESC NULLS LAST
 * ```
 *
 * @package AWSD\Database\Schema\Mapper\SGBD\PostgreSQL
 */
final class OrderByMapper extends AbstractOrderByMapper implements OrderByMapperInterface
{
  /**
   * Builds the ORDER BY clause fragments for PostgreSQL.
   *
   * Returns a one-element array containing the full fragment, e.g.:
   * ["DESC NULLS LAST"]
   *
   * @param OrderByDefinition $order The normalized ORDER BY clause.
   * @return array<int, string> SQL ORDER fragment array.
   */
  public function buildClause(OrderByDefinition $order): array
  {
    $sql = $this->buildDirection($order->direction);

    if ($order->nulls !== null) {
      $sql .= $this->buildNulls($order->nulls);
    }

    return [$sql];
  }

  /**
   * Formats the NULLS placement keyword for PostgreSQL.
   *
   * PostgreSQL supports explicit nulls ordering via:
   * - `NULLS FIRST`
   * - `NULLS LAST`
   *
   * @param string $nulls The nulls modifier: "FIRST" or "LAST"
   * @return string SQL fragment like " NULLS FIRST"
   */
  public function buildNulls(string $nulls): string
  {
    return ' NULLS ' . $nulls;
  }
}
