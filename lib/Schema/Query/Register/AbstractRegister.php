<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Register;

abstract class AbstractRegister implements RegisterInterface
{

  protected string $table;

  /**
   * @param string $table Table name associated with this register (default context)
   */
  public function __construct(string $table)
  {
    $this->table = $table;
  }

  /**
   * Generate a default alias from table and field names.
   *
   * @param string $table
   * @param string $field
   * @return string
   */
  protected function generateAlias(string $table, string $field): string
  {
    return $table . '_' . $field;
  }

  protected function generateExpreAlias(string $expression): string
  {
    $alias = preg_replace('/[^a-zA-Z0-9_]+/', '_', $expression);
    $alias = trim($alias, '_');
    $alias = substr($alias, 0, 30);

    return 'expr_' . strtolower($alias);
  }
}
