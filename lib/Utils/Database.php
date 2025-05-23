<?php

namespace AWSD\Utils;

use PDO;
use PDOException;
use ReflectionClass;
use AWSD\Exception\HttpException;

/**
 * Class Database
 *
 * This class provides methods to manage database connections using environment variables.
 */
class Database
{
  private string $db_driver;
  private string $db_host;
  private string $db_port;
  private string $db_name;
  private string $db_username;
  private string $db_password;

  /**
   * Constructs the Database object and loads the environment configuration.
   *
   * @throws HttpException If any required environment variable is missing.
   */
  public function __construct()
  {
    $this->loadEnvConfig();
  }

  /**
   * Automatically hydrates the class properties with corresponding environment variables.
   *
   * This method uses reflection to set the class properties with values from environment variables.
   *
   * @throws HttpException If any required environment variable is missing.
   */
  private function loadEnvConfig(): void
  {
    $reflection = new ReflectionClass($this);

    foreach ($reflection->getProperties() as $property) {
      $property->setAccessible(true);
      $envKey = strtoupper($property->getName());
      $value = $_ENV[$envKey] ?? null;

      if ($value === null) {
        throw new HttpException("Missing environment variable: {$envKey}", 500);
      }

      $property->setValue($this, $value);
    }
  }

  /**
   * Establishes a connection to the database.
   *
   * This method builds the DSN and creates a new PDO instance to connect to the database.
   *
   * @return PDO The PDO instance representing the database connection.
   * @throws HttpException If the database connection fails.
   */
  public function connect(): PDO
  {
    $dsn = $this->buildDsn();

    try {
      $pdo = new PDO($dsn, $this->db_username, $this->db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]);

      return $pdo;
    } catch (PDOException $e) {
      throw new HttpException("Database connection failed: " . $e->getMessage(), 500);
    }
  }

  /**
   * Builds the Data Source Name (DSN) for the database connection.
   *
   * This method constructs the DSN based on the database driver.
   *
   * @return string The DSN for the database connection.
   * @throws HttpException If the database driver is unsupported.
   */
  private function buildDsn(): string
  {
    switch (strtolower($this->db_driver)) {
      case 'mysql':
      case 'mariadb':
        return "mysql:" . http_build_query([
          'host' => $this->db_host,
          'port' => $this->db_port,
          'dbname' => $this->db_name,
          'charset' => 'utf8mb4'
        ], '', ';');

      case 'pgsql':
        return "pgsql:" . http_build_query([
          'host' => $this->db_host,
          'port' => $this->db_port,
          'dbname' => $this->db_name
        ], '', ';');

      case 'sqlite':
        return "sqlite:" . $this->db_name; // db_name = chemin vers le fichier .sqlite

      default:
        throw new HttpException("Unsupported database driver: {$this->db_driver}", 500);
    }
  }
}
