<?php

declare(strict_types=1);

namespace AWSD\Schema\Config;

use AWSD\Schema\Enum\SqlDialect;

/**
 * Class ORMConfig
 *
 * Central configuration class for the ORM layer.
 * Provides global access to environment-based settings, including SQL dialect.
 * Designed as a singleton to ensure consistency across SQL generation and validation.
 */
final class ORMConfig
{
  /**
   * @var self|null Singleton instance of the configuration.
   */
  private static ?self $instance = null;

  /**
   * @var SqlDialect The SQL dialect inferred from the environment.
   */
  private readonly SqlDialect $dialect;

  /**
   * Private constructor to prevent external instantiation.
   * Initializes the SQL dialect from the DB_DRIVER environment variable.
   */
  private function __construct()
  {
    $this->dialect = SqlDialect::fromEnv($_ENV['DB_DRIVER'] ?? 'pgsql');
  }

  /**
   * Returns the singleton instance of the configuration.
   *
   * @return self
   */
  public static function getInstance(): self
  {
    return self::$instance ??= new self();
  }

  /**
   * Returns the currently configured SQL dialect.
   *
   * @return SqlDialect
   */
  public function getDialect(): SqlDialect
  {
    return $this->dialect;
  }

  public static function reset(): void
  {
    self::$instance = null;
  }

  // Future: add support for schema name, identifier quoting style, etc.
}
