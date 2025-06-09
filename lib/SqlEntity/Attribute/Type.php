<?php

namespace AWSD\SqlEntity\Attribute;

use AWSD\SqlEntity\Enum\TypeEnum;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Type
{
  public function __construct(
    public ?TypeEnum $type = null,
    public bool $primary = false,
    public bool $autoincrement = false,
    public bool $nullable = false,
    public mixed $default = null,
  ) {}
}
