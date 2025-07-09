<?php

namespace AWSD\Database\Schema\Helper;

/**
 * Class StringHelper
 *
 * Utility class for string transformations and naming convention checks.
 *
 * Provides helpers for:
 * - Converting camelCase to snake_case
 * - Validating naming conventions (snake_case)
 * - Inversing snake_case checks
 *
 * Commonly used in the ORM to enforce SQL-safe naming rules (e.g. for table and column names).
 *
 * Example usage:
 * ```php
 * StringHelper::camelToSnake('createdAt'); // 'created_at'
 * StringHelper::isSnakeCase('user_id');    // true
 * ```
 */
class StringHelper
{
  /**
   * Converts a camelCase string to snake_case.
   *
   * Example:
   * 'createdAt' → 'created_at'
   *
   * @param string $input The input camelCase string.
   * @return string The transformed snake_case string.
   */
  public static function camelToSnake(string $input): string
  {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
  }

  /**
   * Checks whether a string is in valid snake_case format.
   *
   * Accepts only lowercase letters and underscores (e.g. 'user_id').
   *
   * @param string $input The string to validate.
   * @return bool True if the string is in snake_case, false otherwise.
   */
  public static function isSnakeCase(string $input): bool
  {
    return preg_match('/^[a-z]+(_[a-z]+)*$/', $input) === 1;
  }

  /**
   * Checks whether a string is *not* in snake_case format.
   *
   * Inverse of isSnakeCase().
   *
   * @param string $input The string to validate.
   * @return bool True if the string is not in snake_case.
   */
  public static function isNotSnakeCase(string $input): bool
  {
    return !self::isSnakeCase($input);
  }
}
