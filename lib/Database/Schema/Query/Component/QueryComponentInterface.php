<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\Component;

/**
 * Interface QueryComponentInterface
 *
 * Defines the contract for SQL query components used in modular query generation.
 * Each component must be able to return its SQL string and associated parameters for binding.
 *
 * @package AWSD\Database\Schema\Query\Component
 */
interface QueryComponentInterface
{
  /**
   * Returns the SQL fragment represented by the component.
   * 
   * Example: "WHERE id = :id AND name IS NOT NULL"
   *
   * @return string The SQL query fragment to be included in the final query.
   */
  public function getQuery(): string;

  /**
   * Returns the parameters to be bound to the query.
   * 
   * Example: [':id' => 42]
   *
   * @return array<string, mixed> An associative array of named parameters and their values.
   */
  public function getParams(): array;
}
