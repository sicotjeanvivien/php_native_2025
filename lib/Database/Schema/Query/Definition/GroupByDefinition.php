<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\definition;

use InvalidArgumentException;

final class GroupByDefinition
{
  public function __construct(
    public readonly array $fields
  ) {
    $this->validate();
  }

  public function validate(): void
  {
    foreach ($this->fields as $field) {
      if (!is_string($field) || trim($field) === '') {
        throw new InvalidArgumentException("GroupBy field must be a non-empty string.");
      }
    }
  }
}
