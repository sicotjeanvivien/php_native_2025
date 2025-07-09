<?php

namespace AWSD\Script\Migration;

use AWSD\Database\Manager\Migration\MigrationManager;
use AWSD\Script\AbstractScript;

/**
 * Class MigrationScript
 *
 * CLI script responsible for executing all pending database migrations.
 * This class extends the base {@see AbstractScript} to benefit from standard
 * initialization (autoload, .env loading, error handling).
 *
 * It delegates the actual migration logic to the {@see \AWSD\Utils\MigrationManager}.
 *
 * Usage (from command line):
 * ```bash
 * php scripts/migrate.php
 * ```
 *
 * @package App\Script
 */
final class RunMigrationScript extends AbstractScript
{
  /**
   * Executes the migration workflow.
   *
   * This method is called internally by {@see execute()} from the base class.
   * It creates a new {@see \AWSD\Utils\MigrationManager} and invokes its {@see migrate()} method.
   *
   * @return void
   */
  protected function run(): void
  {
    $manager = new MigrationManager();
    $manager->migrate();
  }
}
