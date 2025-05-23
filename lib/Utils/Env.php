<?php

namespace AWSD\Utils;

use RuntimeException;

/**
 * Class Env
 *
 * This class is responsible for loading environment variables from a .env file.
 */
class Env
{
  /**
   * Constructs the Env object and loads the environment variables from the .env file.
   *
   * @throws RuntimeException If the .env file does not exist.
   */
  public function __construct()
  {
    $this->loadEnv(dirname(__DIR__, 2) . '/.env');
  }

  /**
   * Loads environment variables from the specified .env file.
   *
   * This method reads the .env file and sets the environment variables.
   *
   * @param string $path The path to the .env file.
   * @throws RuntimeException If the .env file does not exist or cannot be read.
   */
  public function loadEnv(string $path): void
  {
    if (!file_exists($path)) {
      throw new RuntimeException("The .env file does not exist.");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
      throw new RuntimeException("Unable to read the .env file.");
    }

    foreach ($lines as $line) {
      if (strpos(trim($line), '#') === 0) {
        continue;
      }

      $parts = explode('=', $line, 2);
      if (count($parts) < 2) {
        continue;
      }

      $name = trim($parts[0]);
      $value = trim($parts[1]);
      $value = trim($value, "\"'");

      if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
      }
    }
  }
}
