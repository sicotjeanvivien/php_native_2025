<?php

namespace AWSD\Schema\Migration;

use AWSD\Database\QueryExecutor;
use AWSD\Schema\Migration\Migration;
use AWSD\Schema\EntitySchemaBuilder;
use AWSD\Schema\Scanner\EntityScanner;
use AWSD\Utils\Log;
use RuntimeException;

/**
 * Class MigrationManager
 *
 * Manages the generation and execution of SQL migration scripts.
 * 
 * Responsibilities:
 * - Scans entity classes and generates `CREATE TABLE` SQL files (`generate()`).
 * - Applies new SQL migration files in `/migrations` directory (`migrate()`).
 * - Tracks applied migrations in a `migrations` table.
 *
 * File format: each migration must be a `.sql` file named like:
 *   `YYYYMMDD_HHMMSS_create_table_name.sql`
 *
 * Usage:
 *   $manager = new MigrationManager();
 *   $manager->generate(); // Generates SQL from entities
 *   $manager->migrate();  // Applies all new migrations
 *
 * Dependencies:
 * - Entity classes must be located in `/src/Model`
 * - Migration history is tracked in a table named `migrations`
 */

class MigrationManager
{

  private const MIGRATION_PATH =  ROOT_PATH . '/migrations';
  private const ENTITY_PATH = ROOT_PATH . '/src/Model';

  /**
   * @var QueryExecutor Handles execution of SQL queries with proper binding and entity hydration.
   */
  protected QueryExecutor $queryExecutor;

  /**
   * Constructs the migration manager and initializes the query executor.
   */
  public function __construct()
  {
    $this->queryExecutor = new QueryExecutor(null);
  }

  public function generate(): void
  {
    $entities = EntityScanner::findEntities(self::ENTITY_PATH);
    foreach ($entities as $entity) {
      $sql = $this->generateRequestCreate($entity);
      $tableName = strtolower((new \ReflectionClass($entity))->getShortName()) . 's';
      $this->generateFile($tableName, $sql);
    }
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


  private function generateRequestCreate(object $entity): string
  {
    $builder = new EntitySchemaBuilder($entity);
    return $builder->create();
  }


  private function generateFile(string $tableName, string $sql): void
  {
    $timestamp = date('Ymd_His');
    $filename = "{$timestamp}_create_{$tableName}_table.sql";
    $path = self::MIGRATION_PATH . "/$filename";

    file_put_contents($path, "-- Migration auto-générée\n$sql");

    echo "✅ Migration generated: $filename\n";
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
    $entityBuilder = new EntitySchemaBuilder((new Migration()));
    $migrationExecuted = $entityBuilder->findAll(["filename"]);

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
      if ($this->queryExecutor->executeRaw($migrationQuery)) {
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
    $this->queryExecutor->executeNonQuery("INSERT INTO migrations (filename) VALUES (:filename)", [":filename" => $migration]);
  }

  /**
   * Creates the migrations table if it does not exist.
   */
  private function createMigrationTable(): void
  {

    $migration = new Migration();

    $schemaBuilder =  new EntitySchemaBuilder($migration);
    $querySqlCreate = $schemaBuilder->create();

    $this->queryExecutor->executeNonQuery($querySqlCreate);
  }
}
