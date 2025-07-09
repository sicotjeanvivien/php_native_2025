<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\definition;

final class FieldDefinition
{
  public function __construct(
    public readonly string $table,
    public readonly string $column,
    public readonly ?string $alias
  ) {}
}
