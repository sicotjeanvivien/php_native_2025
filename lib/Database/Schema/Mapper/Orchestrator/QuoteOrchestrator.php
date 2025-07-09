<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\Orchestrator;

use AWSD\Database\Schema\Mapper\SGBD\PostgreSQL\QuoteMapper as PostgreSQLQuoteMapper;
use AWSD\Database\Schema\Mapper\SGBD\MySQL\QuoteMapper as MySQLQuoteMapper;
use AWSD\Database\Schema\Mapper\SGBD\SQLite\QuoteMapper as SQLiteQuoteMapper;

final class QuoteOrchestrator extends AbstractOrchestrator
{
  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();

    $this->sgbdMapper = $this->getSgbdImplementation([
      'pgsql'  => new PostgreSQLQuoteMapper(),
      'mysql'  => new MySQLQuoteMapper(),
      'sqlite' => new SQLiteQuoteMapper(),
    ]);
  }

  public function quoteIdentifier(string $identifier): string
  {
    return $this->sgbdMapper->quoteIdentifier($identifier);
  }


  public function quoteAlias(string $identifier, string $alias): string
  {
    return $this->sgbdMapper->quoteAlias($identifier, $alias);
  }

  public function quoteValue(mixed $value): string
  {
    return $this->sgbdMapper->quoteValue($value);
  }
}
