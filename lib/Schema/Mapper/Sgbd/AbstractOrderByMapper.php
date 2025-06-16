<?php

declare(strict_types=1);

namespace AWSD\Schema\Mapper\SGBD;

/**
 * Class AbstractOrderByMapper
 *
 * Provides reusable validation methods for ORDER BY clause construction.
 * Used by SGBD-specific mappers to ensure SQL direction and NULLS keywords are valid.
 *
 * @package AWSD\Schema\Mapper\SGBD
 */
abstract class AbstractOrderByMapper
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
   * Returns the SQL sorting direction (ASC or DESC).
   *
   * @param string $direction The requested direction.
   * @return string
   * @throws \RuntimeException If the direction is invalid.
   */
  public function buildDirection(string $direction): string
  {
    if ($this->isDirectionInvalid($direction)) {
      throw new \RuntimeException("Invalid ORDER BY direction: '$direction'");
    }
    return $direction;
  }

  /**
   * Checks if a given direction is valid (ASC or DESC).
   *
   * @param string $direction
   * @return bool
   */
  protected function isDirectionValid(string $direction): bool
  {
    return in_array($direction, self::VALID_DIRECTIONS, true);
  }

  /**
   * Checks if a given direction is invalid.
   *
   * @param string $direction
   * @return bool
   */
  protected function isDirectionInvalid(string $direction): bool
  {
    return !$this->isDirectionValid($direction);
  }

  /**
   * Checks if a given NULLS modifier is valid (FIRST or LAST).
   *
   * @param string $nulls
   * @return bool
   */
  protected function isNullsValid(string $nulls): bool
  {
    return in_array($nulls, self::VALID_NULLS, true);
  }

  /**
   * Checks if a given NULLS modifier is invalid.
   *
   * @param string $nulls
   * @return bool
   */
  protected function isNullsInvalid(string $nulls): bool
  {
    return !$this->isNullsValid($nulls);
  }
}
