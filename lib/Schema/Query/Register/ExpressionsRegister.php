<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Register;

use AWSD\Schema\Query\definition\ExpressionDefinition;

final class ExpressionsRegister extends AbstractRegister
{

  /** @var ExpressionDefinition[] List of registered fields */
  private array $expresssions = [];

  public function __construct(string $table)
  {
    parent::__construct($table);
  }


  public function register(string|array $expression): void
  {

    match (true) {
      is_string($expression)  => $this->addExpressionDefinition($expression, null),
      is_array($expression)   => $this->processExpressionArray($expression),
      default                 => throw new \InvalidArgumentException("Unsupported expression format.")
    };
  }

  public function getAll(): array
  {
    return $this->expresssions;
  }

  private function addExpressionDefinition(string $expression, ?string $alias): void
  {
    if (empty($alias)) {
      $alias = $this->generateExpreAlias($expression);
    }
    $this->expresssions[] = new ExpressionDefinition($expression, $alias);
  }

  private function processExpressionArray(array $expressions): void
  {
    foreach ($expressions as $expr => $alias) {
      if (!is_string($expr) || !is_string($alias)) {
        throw new \InvalidArgumentException("Invalid expression => alias pair.");
      }
      $this->addExpressionDefinition($expr, $alias);
    }
  }
}
