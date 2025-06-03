<?php

namespace AWSD\Utils;

/**
 * Class Sanitization
 *
 * Provides methods for sanitizing input data to prevent XSS and other security vulnerabilities.
 */
class Sanitization
{
  /**
   * Sanitizes global input arrays ($_GET, $_POST, $_COOKIE, $_REQUEST).
   *
   * This method applies sanitization to all elements in the global input arrays to ensure they are safe for further processing.
   */
  public static function sanitizeGlobals(): void
  {
    $_GET = self::clean($_GET);
    $_POST = self::clean($_POST);
    $_COOKIE = self::clean($_COOKIE);
  }

  /**
   * Cleans an array by applying sanitization to each element.
   *
   * @param array $array The array to clean.
   * @return array The cleaned array.
   */
  public static function clean(array $array): array
  {
    return array_map([self::class, 'sanitize'], $array);
  }

  /**
   * Sanitizes a value by trimming, stripping tags, and converting special characters to HTML entities.
   *
   * @param mixed $value The value to sanitize.
   * @return mixed The sanitized value.
   */
  private static function sanitize(mixed $value): mixed
  {
    if (is_array($value)) {
      return self::clean($value);
    }

    $value = trim($value);
    $value = strip_tags($value);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
  }
}
