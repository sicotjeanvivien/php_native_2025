<?php

namespace AWSD\Database\Schema\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Trigger
{
  public function __construct(
    public bool $onUpdate = false
  ) {}
}
