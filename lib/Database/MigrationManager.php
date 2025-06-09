<?php

namespace AWSD\Database;

use AWSD\Model\Migration;
use AWSD\SqlEntity\Query;
use AWSD\SqlEntity\SqlEntityGenerator;
use AWSD\Utils\Log;
use RuntimeException;

/**
 * Class MigrationManager
 *
 * Handles execution of SQL migration scripts from a designated folder.
 * Automatically tracks which migrations have been applied using a `migrations` table.
 *
 * Expected file structure: each migration must be a `.sql` file inside `/migrations/`.
 * Each file is executed once and its filename is recorded in the migrations table.
 *
 * Usage:
 *   $manager = new MigrationManager();
 *   $manager->migrate();
 */
class MigrationManager
{

  private const MIGRATION_PATH =  ROOT_PATH . '/migrations';

  private const QUERY_CREATE_TABLE_MIGRATION = "CREATE TABLE IF NOT EXISTS migrations (
  id SERIAL PRIMARY KEY,
  filename VARCHAR(255) NOT NULL UNIQUE,
  executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";


  /**
   * @var QueryExecutor Handles execution of SQL queries with proper binding and entity hydration.
   */
  protected QueryExecutor $queryExecutor;

  /**
   * Constructs the migration manager and initializes the query executor.
   */
  public function __construct()
  {
    $this->queryExecutor = new QueryExecutor();
  }

  /**
   * Applies all pending migrations found in the /migrations directory.
   *
   * Scans for new SQL files, compares with already applied ones,
   * and runs each new migration in order.
   */
  public function migrate(): void
  {
    $migrationsToRun  = $this->scanMigrations();
    $this->executeMigrations($migrationsToRun);
  }

  /**
   * Scans the migration folder and returns a list of new migrations to apply.
   *
   * @return array An array of `.sql` filenames that haven't been applied yet.
   */
  private function scanMigrations(): array
  {
    $this->createMigrationTable();
    $migrationsInFolder = array_filter(scandir(self::MIGRATION_PATH), fn($f) => str_ends_with($f, '.sql'));
    $migrationExecuted = $this->queryExecutor->fetchAllColumn("SELECT filename FROM migrations;");
    return array_diff($migrationsInFolder, $migrationExecuted);
  }

  /**
   * Executes the given list of migration files.
   *
   * For each migration:
   * - Ensures file exists
   * - Loads and validates its SQL content
   * - Executes the query
   * - Logs execution and marks it as applied in the DB
   *
   * @param array $migrationsToRun List of migration filenames to run.
   * @throws \RuntimeException If a file is missing or empty.
   */
  private function executeMigrations(array $migrationsToRun): void
  {
    sort($migrationsToRun);
    foreach ($migrationsToRun as $migration) {
      $migrationPath = self::MIGRATION_PATH . "/{$migration}";
      if (!file_exists($migrationPath)) {
        throw new RuntimeException("Migration file not found: $migrationPath");
      }
      $migrationQuery = file_get_contents($migrationPath);
      if (!trim($migrationQuery)) {
        throw new RuntimeException("Migration file is empty: $migrationPath");
      }
      if ($this->queryExecutor->executeNonQuery($migrationQuery)) {
        $this->saveMigrationExecuted($migration);
        Log::logToConsole("[Migration] Applied: {$migration}");
      }
    }
  }

  /**
   * Records a migration as executed in the migrations table.
   *
   * @param string $migration The migration filename to record.
   */
  private function saveMigrationExecuted(string $migration): void
  {
    var_dump("= = =");
    $this->queryExecutor->executeNonQuery("INSERT INTO migrations (filename) VALUES (:filename)", [":filename" => $migration]);
  }

  /**
   * Creates the migrations table if it does not exist.
   */
  private function createMigrationTable(): void
  {

    $migration = new Migration();

    $SqlEntityGenrator =  new SqlEntityGenerator($migration);
    $querySqlCreate = $SqlEntityGenrator->create();

    $this->queryExecutor->executeNonQuery($querySqlCreate);
  }
}
