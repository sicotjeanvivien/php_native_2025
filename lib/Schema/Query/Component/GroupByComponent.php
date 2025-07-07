<?php

namespace AWSD\Schema\Query\Component;

use AWSD\Schema\Query\definition\GroupByDefinition;

final class GroupByComponent extends AbstractQueryComponent
{

  private  GroupByDefinition $groupBy;

  public function __construct(array $groupBy)
  {
    $this->groupBy = (new GroupByDefinition($groupBy));
  }


  public function getQuery(): string
  {
    return 'GROUP BY ' . implode(', ', $this->groupBy->fields);
  }
}
