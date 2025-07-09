<?php

namespace AWSD\Database\Schema\Query\Component;

use AWSD\Database\Schema\Query\definition\GroupByDefinition;

final class GroupByComponent extends AbstractQueryComponent
{

  private  GroupByDefinition $groupBy;

  public function __construct(array $groupBy)
  {
    parent::__construct();
    $this->groupBy = (new GroupByDefinition($groupBy));
  }

  public function getQuery(): string
  {
    if (empty($this->groupBy->fields)) return '';

    $quotedFields = array_map(
      fn(string $field) => $this->quote->quoteIdentifier($field),
      $this->groupBy->fields
    );
    return 'GROUP BY ' . implode(', ', $quotedFields);
  }
}
