<?php

namespace AWSD\Database\Schema\Query;

interface QueryInterface
{
  /**
   * Accepts an entity object for SQL generation.
   *
   * @param object $entity The entity from which SQL should be generated.
   */
  public function __construct(string $entity);

  public function generateSql(): string;
}
