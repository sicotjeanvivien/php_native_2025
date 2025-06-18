<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\MySQL;

use AWSD\Schema\Mapper\SGBD\AbstractOrderByMapper;
use AWSD\Schema\Mapper\SGBD\interface\OrderByMapperInterface;
use AWSD\Schema\Query\definition\OrderByDefinition;

/**
 * Class OrderByMapper (MySQL)
 *
 * Formats ORDER BY clause fragments in MySQL-compatible syntax.
 * Since MySQL does not natively support `NULLS FIRST` or `NULLS LAST`,
 * this mapper emulates the behavior using `IS NULL` sorting tricks.
 *
 * ---
 * Emulation strategy:
 * - `NULLS FIRST` ⇒ `IS NULL DESC`
 * - `NULLS LAST`  ⇒ `IS NULL ASC`
 * Combined with the standard direction clause: `field ASC|DESC`
 *
 * ---
 * Example output:
 * ```sql
 * ORDER BY created_at IS NULL DESC, created_at DESC
 * ```
 *
 * @package AWSD\Schema\Mapper\SGBD\MySQL
 */
final class OrderByMapper extends AbstractOrderByMapper implements OrderByMapperInterface
{
  /**
   * Builds the ORDER BY clause fragments for MySQL.
   *
   * If a nulls placement is specified (`FIRST` or `LAST`),
   * it prepends a clause like `field IS NULL ASC|DESC` to emulate PostgreSQL behavior.
   * Then appends the classic `field ASC|DESC` direction.
   *
   * @param OrderByDefinition $order The normalized ORDER BY clause definition.
   * @return array<int, string> List of SQL fragments to be concatenated.
   */
  public function buildClause(OrderByDefinition $order): array
  {
    $fragments = [];

    if ($order->nulls !== null) {
      $fragments[] = $this->buildNulls($order->nulls);
    }

    $fragments[] = $this->buildDirection($order->direction);

    return $fragments;
  }

  /**
   * Emulates PostgreSQL-style NULLS placement in MySQL.
   *
   * MySQL does not support `NULLS FIRST/LAST`, so we sort explicitly on `IS NULL`:
   * - `NULLS FIRST` → rows where `field IS NULL` appear first (IS NULL DESC)
   * - `NULLS LAST`  → rows where `field IS NULL` appear last (IS NULL ASC)
   *
   * @param string $nulls The NULLS placement keyword: "FIRST" or "LAST"
   * @return string SQL fragment like `IS NULL ASC`
   */
  public function buildNulls(string $nulls): string
  {
    return match ($nulls) {
      'FIRST' => 'IS NULL DESC',
      'LAST'  => 'IS NULL ASC',
    };
  }
}
