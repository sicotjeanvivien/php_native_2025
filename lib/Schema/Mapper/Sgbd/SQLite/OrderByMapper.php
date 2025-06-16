<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD\SQLite;

use AWSD\Schema\Mapper\SGBD\AbstractOrderByMapper;
use AWSD\Schema\Mapper\SGBD\interface\OrderByMapperInterface;

/**
 * Class OrderByMapper (SQLite)
 *
 * Builds SQL ORDER BY fragments compatible with SQLite.
 * Ignores NULLS FIRST/LAST modifiers since SQLite does not support them explicitly.
 *
 * @package AWSD\Schema\Mapper\SGBD\SQLite
 */
final class OrderByMapper extends AbstractOrderByMapper implements OrderByMapperInterface
{
  /**
   * Emits a warning if NULLS modifier is used (SQLite ignores them).
   */
  public const WARN_UNSUPPORTED_NULLS = true;

  /**
   * Builds the ORDER BY clause for SQLite.
   * Ignores NULLS modifier since SQLite handles NULLs implicitly.
   *
   * @param string $direction Either "ASC" or "DESC".
   * @param string|null $nulls Ignored for SQLite.
   * @return array<int, string>
   */
  public function buildClause(string $direction, ?string $nulls = null): array
  {
    return [$this->buildDirection($direction)];
  }

  /**
   * Emits a warning if a NULLS modifier is requested.
   *
   * @param string $nulls Either "FIRST" or "LAST".
   * @return string Always returns empty string.
   */
  public function buildNulls(string $nulls): string
  {
    if (self::WARN_UNSUPPORTED_NULLS) {
      trigger_error("NULLS '$nulls' is not supported in SQLite. Ignored.", E_USER_WARNING);
    }

    return '';
  }
}
