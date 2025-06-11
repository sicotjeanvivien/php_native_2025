<?php

namespace AWSD\Schema\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Trigger
{
  public function __construct(
    public bool $onUpdate = false
  ) {}
}
