<?php

declare(strict_types=1);

namespace AWSD\Database;

use PDO;
use PDOException;
use ReflectionClass;
use AWSD\Exception\HttpException;

/**
 * Class Database
 *
 * Provides a database abstraction layer that supports multiple drivers (MySQL, PostgreSQL, SQLite).
 * Configuration is loaded dynamically from environment variables via reflection.
 * This class also implements the singleton pattern for PDO connection reuse.
 */
class Database
{
  /**
   * @var PDO|null Singleton PDO instance shared across the application.
   */
  private static ?PDO $instance = null;

  /**
   * @var string|null The database driver (e.g. mysql, pgsql, sqlite).
   */
  private ?string $db_driver;

  /**
   * @var string|null The database host (only for MySQL/PostgreSQL).
   */
  private ?string $db_host;

  /**
   * @var string|null The database port (only for MySQL/PostgreSQL).
   */
  private ?string $db_port;

  /**
   * @var string|null The database name or file path (depending on driver).
   */
  private ?string $db_name;

  /**
   * @var string|null The username for database authentication.
   */
  private ?string $db_username;

  /**
   * @var string|null The password for database authentication.
   */
  private ?string $db_password;

  /**
   * Returns the shared PDO instance (singleton).
   *
   * If the connection has not yet been initialized, it is created.
   *
   * @return PDO The shared database connection.
   * @throws HttpException If connection fails.
   */
  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      $db = new self();
      self::$instance = $db->connect();
    }

    return self::$instance;
  }

  /**
   * Database constructor.
   *
   * Loads environment configuration and prepares connection parameters.
   */
  public function __construct()
  {
    $this->loadEnvConfig();
  }

  /**
   * Establishes a PDO connection using the configured driver and credentials.
   *
   * @return PDO The active PDO connection.
   * @throws HttpException If the connection fails.
   */
  public function connect(): PDO
  {
    $dsn = $this->buildDsn();

    try {
      return new PDO($dsn, $this->db_username, $this->db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    } catch (PDOException $e) {
      throw new HttpException("Database connection failed: " . $e->getMessage(), 500);
    }
  }

  /**
   * Loads environment configuration by setting all properties dynamically.
   *
   * Uses reflection to match private property names to $_ENV variables.
   */
  private function loadEnvConfig(): void
  {
    $reflection = new ReflectionClass($this);
    foreach ($reflection->getProperties() as $property) {
      $property->setAccessible(true);
      $envKey = strtoupper($property->getName());
      $property->setValue($this, $_ENV[$envKey] ?? null);
    }
  }

  /**
   * Builds the Data Source Name (DSN) string based on the driver and configuration.
   *
   * @return string The constructed DSN string.
   * @throws HttpException If the driver is unsupported or a required config is missing.
   */
  private function buildDsn(): string
  {
    switch (strtolower($this->db_driver)) {
      case 'mysql':
      case 'mariadb':
        $this->requireEnvFields(['db_host', 'db_port', 'db_name', 'db_username', 'db_password']);
        return 'mysql:' . http_build_query([
          'host' => $this->db_host,
          'port' => $this->db_port,
          'dbname' => $this->db_name,
          'charset' => 'utf8mb4',
        ], '', ';');

      case 'pgsql':
        $this->requireEnvFields(['db_host', 'db_port', 'db_name', 'db_username', 'db_password']);
        return 'pgsql:' . http_build_query([
          'host' => $this->db_host,
          'port' => $this->db_port,
          'dbname' => $this->db_name,
        ], '', ';');

      case 'sqlite':
        $this->requireEnvFields(['db_name']);
        return 'sqlite:' . $this->db_name;

      default:
        throw new HttpException("Unsupported database driver: {$this->db_driver}", 500);
    }
  }

  /**
   * Verifies that all required configuration fields are defined.
   *
   * @param array $fields List of property names to validate (e.g. 'db_host').
   * @throws HttpException If any required value is missing or undefined.
   */
  private function requireEnvFields(array $fields): void
  {
    $reflectionClass = new ReflectionClass($this);

    foreach ($fields as $field) {
      if ($reflectionClass->hasProperty($field)) {
        $property = $reflectionClass->getProperty($field);
        $property->setAccessible(true);
        if ($property->getValue($this) === null) {
          throw new HttpException("Missing required environment variable: " . strtoupper($field), 500);
        }
      } else {
        throw new HttpException("Unknown configuration field: {$field}", 500);
      }
    }
  }
}
