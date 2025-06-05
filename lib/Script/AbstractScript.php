<?php

namespace AWSD\Script;

use AWSD\Utils\Env;
use AWSD\Utils\Log;

/**
 * Class AbstractScript
 *
 * Abstract base class for CLI scripts. This class encapsulates common setup routines,
 * such as defining the application root path, initializing autoloaders, loading
 * environment variables, and managing structured error handling.
 *
 * Child classes must implement the {@see run()} method to define their execution logic.
 * Use {@see execute()} as the public entry point to run the script safely.
 *
 * @package AWSD\Script
 */
abstract class AbstractScript
{
  /**
   * Constructs the script and initializes application context:
   * - Defines the ROOT_PATH constant if not already set.
   * - Registers the autoloader via /lib/init.php.
   * - Loads environment variables from .env using Env.
   */
  public function __construct()
  {
    $this->initRoot();
    $this->initAutoload();
    $this->initEnv();
  }

  /**
   * Abstract method that child scripts must implement to perform their logic.
   *
   * This method is called internally by {@see execute()} after environment setup.
   */
  abstract protected function run(): void;

  /**
   * Executes the script by calling {@see run()}, handling exceptions and logging errors.
   *
   * Outputs a success message on completion or an error message if an exception occurs.
   * Terminates the script with an exit code of 1 on failure.
   *
   * @return void
   */
  public function execute(): void
  {
    try {
      $this->run();
      echo "✅ Script completed.\n";
    } catch (\Throwable $e) {
      echo "❌ Error: {$e->getMessage()}\n";
      Log::captureError($e);
      exit(1);
    }
  }

  /**
   * Defines the ROOT_PATH constant based on the script location, if not already set.
   *
   * ROOT_PATH is used throughout the application to resolve absolute paths.
   *
   * @return void
   */
  private function initRoot(): void
  {
    if (!defined('ROOT_PATH')) {
      define('ROOT_PATH', dirname(__DIR__, 2));
    }
  }

  /**
   * Loads the application's environment variables from the .env file.
   *
   * Uses the Env class to populate $_ENV.
   *
   * @return void
   */
  private function initEnv(): void
  {
    new Env();
  }

  /**
   * Includes the autoloader initialization script located in /lib/init.php.
   *
   * This registers the PSR-compatible autoloader and sets alias mappings.
   *
   * @return void
   */
  private function initAutoload(): void
  {
    require_once ROOT_PATH . '/lib/init.php';
  }
}
