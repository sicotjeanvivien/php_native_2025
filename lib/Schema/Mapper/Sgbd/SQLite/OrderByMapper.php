<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\SQLite;

use AWSD\Schema\Mapper\SGBD\AbstractOrderByMapper;
use AWSD\Schema\Mapper\SGBD\interface\OrderByMapperInterface;
use AWSD\Schema\Query\definition\OrderByDefinition;

/**
 * Class OrderByMapper (SQLite)
 *
 * Formats ORDER BY clause fragments for SQLite-compatible SQL syntax.
 * Since SQLite does not support `NULLS FIRST` or `NULLS LAST` explicitly,
 * this mapper silently ignores the nulls directive or emits a warning (optional).
 *
 * ---
 * Behavior:
 * - `ORDER BY field ASC|DESC` is always honored
 * - `NULLS FIRST/LAST` are ignored but optionally warned
 *
 * ---
 * Example output:
 * ```sql
 * ORDER BY created_at DESC
 * ```
 *
 * @package AWSD\Schema\Mapper\SGBD\SQLite
 */
final class OrderByMapper extends AbstractOrderByMapper implements OrderByMapperInterface
{
  /**
   * Whether to emit a warning when a NULLS modifier is ignored.
   */
  public const WARN_UNSUPPORTED_NULLS = true;

  /**
   * Builds the ORDER BY clause fragment for SQLite.
   *
   * Ignores the `nulls` directive entirely since SQLite does not support
   * `NULLS FIRST/LAST`. Only the direction is retained.
   *
   * @param OrderByDefinition $order The ORDER BY clause definition.
   * @return array<int, string> SQL fragment like ["DESC"]
   */
  public function buildClause(OrderByDefinition $order): array
  {
    return [$this->buildDirection($order->direction)];
  }

  /**
   * Emits a warning (optional) and ignores the NULLS clause.
   *
   * This method is present to maintain a consistent interface,
   * but always returns an empty string under SQLite.
   *
   * @param string $nulls The NULLS directive ("FIRST" or "LAST")
   * @return string Always an empty string for SQLite
   */
  public function buildNulls(string $nulls): string
  {
    if (self::WARN_UNSUPPORTED_NULLS) {
      trigger_error("NULLS '$nulls' is not supported in SQLite. Ignored.", E_USER_WARNING);
    }

    return '';
  }
}
