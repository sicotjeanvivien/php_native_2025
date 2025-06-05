<?php

namespace AWSD\Database\Query;

class DeleteQuery implements QueryInterface
{

  public function __construct(private object $entity) {}

  public function generateSql(): string
  {
    return '';
  }
}
