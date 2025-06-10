<?php

namespace AWSD\Schema\Enum;

/**
 * SqlDialect
 *
 * Enumerates supported SQL dialects used to map entity field types and constraints.
 * This enum serves as the basis for selecting the appropriate SQL mapper (MySQL, PostgreSQL, SQLite).
 */
enum SqlDialect: string
{
/** MySQL dialect (e.g., for use with InnoDB, utf8mb4, AUTO_INCREMENT) */
  case MYSQL  = 'mysql';

/** PostgreSQL dialect (supports SERIAL, JSONB, UUID natively) */
  case PGSQL  = 'pgsql';

/** SQLite dialect (typeless, supports TEXT affinity and implicit rowid keys) */
  case SQLITE = 'sqlite';

  /**
   * Resolves the current SQL dialect from the environment variable DB_DRIVER.
   *
   * @return self The corresponding SQL dialect.
   * @throws \RuntimeException If the environment variable is missing or invalid.
   */
  public static function fromEnv(string $driver): self
  {
    return match ($driver) {
      'mysql'  => self::MYSQL,
      'pgsql'  => self::PGSQL,
      'sqlite' => self::SQLITE,
      default  => throw new \RuntimeException(
        "Unsupported DB driver: {$driver} "
      ),
    };
  }
}
