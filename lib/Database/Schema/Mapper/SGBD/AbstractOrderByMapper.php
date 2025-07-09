<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\SGBD;

/**
 * Class AbstractOrderByMapper
 *
 * Provides reusable validation methods for ORDER BY clause construction.
 * Used by SGBD-specific mappers to ensure SQL direction and NULLS keywords are valid.
 *
 * @package AWSD\Database\Schema\Mapper\SGBD
 */
abstract class AbstractOrderByMapper
{

  /**
   * Returns the SQL sorting direction (ASC or DESC).
   *
   * @param string $direction The requested direction.
   * @return string
   */
  public function buildDirection(string $direction): string
  {
    return $direction;
  }
}
