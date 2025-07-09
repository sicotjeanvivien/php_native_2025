<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\definition;

final class ExpressionDefinition
{
  public function __construct(
    public readonly string $expression,
    public readonly string $alias
  ) {}
}
