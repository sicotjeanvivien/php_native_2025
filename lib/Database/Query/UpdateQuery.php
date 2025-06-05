<?php

namespace AWSD\Database\Query;

class UpdateQuery implements QueryInterface
{

  public function __construct(private object $entity) {}

  public function generateSql(): string
  {
    return '';
  }
}
