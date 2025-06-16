<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\MySQL;

use AWSD\Schema\Mapper\SGBD\AbstractOrderByMapper;
use AWSD\Schema\Mapper\SGBD\interface\OrderByMapperInterface;

/**
 * Class OrderByMapper (MySQL)
 *
 * Builds SQL ORDER BY fragments compatible with MySQL syntax.
 * Emulates PostgreSQL's NULLS FIRST / LAST using IS NULL tricks.
 *
 * @package AWSD\Schema\Mapper\SGBD\MySQL
 */
final class OrderByMapper extends AbstractOrderByMapper implements OrderByMapperInterface
{
  /**
   * Builds the ORDER BY clause fragments.
   * Uses "field IS NULL ASC|DESC" + "field ASC|DESC".
   *
   * @param string $direction Either "ASC" or "DESC"
   * @param string|null $nulls Either "FIRST", "LAST", or null
   * @return array<int, string> List of ORDER BY fragments
   * @throws \RuntimeException If direction or nulls are invalid
   */
  public function buildClause(string $direction, ?string $nulls = null): array
  {
    $fragments = [];

    if ($nulls !== null) {
      $fragments[] = $this->buildNulls($nulls);
    }

    $fragments[] = $this->buildDirection($direction);

    return $fragments;
  }

  /**
   * Emulates NULLS FIRST / LAST using IS NULL ordering.
   *
   * @param string $nulls Either "FIRST" or "LAST"
   * @return string SQL fragment like "IS NULL ASC"
   * @throws \RuntimeException If nulls value is invalid
   */
  public function buildNulls(string $nulls): string
  {
    if ($this->isNullsInvalid($nulls)) {
      throw new \RuntimeException("Invalid NULLS ordering keyword: '$nulls'");
    }

    return match ($nulls) {
      'FIRST' => 'IS NULL DESC',
      'LAST'  => 'IS NULL ASC',
    };
  }
}
