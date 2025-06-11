<?php

namespace AWSD\Schema\Query;

final class IndexQuery extends AbstractQuery
{

  public function __construct(object $entity)
  {
    parent::__construct($entity, []);
  }

  public function generateSql(): string
  {
    return "";
  }
}
