<?php

namespace AWSD\Database\Query;

class InsertQuery implements QueryInterface
{

  public function __construct(private object $entity) {}

  public function generateSql(): string
  {
    return '';
  }
}
