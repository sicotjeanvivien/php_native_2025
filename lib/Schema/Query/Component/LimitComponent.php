<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Component;

/**
 * Class LimitComponent
 *
 * Represents the SQL LIMIT clause with a single bindable parameter.
 *
 * @package AWSD\Schema\Query\Component
 */
final class LimitComponent extends AbstractQueryComponent
{
  private int $limit;

  /**
   * Constructor
   *
   * @param int $limit The maximum number of results to return.
   */
  public function __construct(int $limit)
  {
    $this->limit = $limit;
  }

  /**
   * Returns the SQL LIMIT clause with a placeholder.
   *
   * @return string SQL string (e.g., "LIMIT :limit").
   */
  public function getQuery(): string
  {
    $placeholder = $this->generatePlaceholder('limit');
    $this->registerParam($placeholder, $this->limit);
    return <<<SQL
      LIMIT $placeholder
    SQL;
  }
}
