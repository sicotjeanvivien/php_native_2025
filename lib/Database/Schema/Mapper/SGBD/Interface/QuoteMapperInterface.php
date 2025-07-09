<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Mapper\SGBD\interface;

interface QuoteMapperInterface
{
  public function quoteIdentifier(string $identifier): string;
  public function quoteAlias(string $identifier, string $alias): string;
  public function quoteValue(mixed $value): string;
}
