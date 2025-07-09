<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\Component;

/**
 * Class OffsetComponent
 *
 * Represents the SQL OFFSET clause with a single bindable parameter.
 *
 * @package AWSD\Database\Schema\Query\Component
 */
final class OffsetComponent extends AbstractQueryComponent
{
  private int $offset;

  /**
   * Constructor
   *
   * @param int $offset The number of rows to skip.
   */
  public function __construct(int $offset)
  {
    $this->offset = $offset;
  }

  /**
   * Returns the SQL OFFSET clause with a placeholder.
   *
   * @return string SQL string (e.g., "OFFSET :offset").
   */
  public function getQuery(): string
  {
    $placeholder = $this->generatePlaceholder('offset');
    $this->registerParam($placeholder, $this->offset);

    return "OFFSET $placeholder";
  }
}
