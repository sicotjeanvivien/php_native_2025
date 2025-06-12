<?php

namespace AWSD\Schema\Query\Component;

final class WhereComponent implements QueryComponentInterface
{
  private array $params = [];
  private array $conditions;

  public function __construct(array $conditions)
  {
    $this->conditions = $conditions;
  }

  public function build(): array
  {
    if (empty($this->conditions)) {
      return ['sql' => '', 'params' => []];
    }

    $clauses = $this->getClauses();
    return [
      'sql' => 'WHERE ' . implode(' AND ', $clauses),
      'params' => $this->params
    ];
  }

  private function getClauses(): array
  {
    $clauses = [];
    foreach ($this->conditions as $field => $condition) {
      $clauses[] = match (true) {
        is_array($condition)  => $this->getArrayCondition($field, $condition),
        is_null($condition)   => $this->getNullCondition($field),
        default               => $this->getDefaultCondition($field, $condition)
      };
    }
    return $clauses;
  }

  private function setParam(string $field, mixed $value): string
  {
    $placeholder = $this->getPlaceholder($field);
    $this->params[$placeholder] = $value;
    return $placeholder;
  }

  private function getPlaceholder(string $field, int $suffix = 0): string
  {
    $placeholder = ':' . $field . ($suffix ? "_$suffix" : '');
    if (array_key_exists($placeholder, $this->params)) {
      return $this->getPlaceholder($field, ++$suffix);
    }
    return $placeholder;
  }

  private function getArrayCondition(string $field, array $condition): string
  {
    $operator = strtoupper($condition['operator'] ?? '=');
    $value = $condition['value'] ?? null;
    $placeholder = $this->setParam($field, $value);
    return "$field $operator $placeholder";
  }

  private function getNullCondition(string $field): string
  {
    return "$field IS NULL";
  }

  private function getDefaultCondition(string $field, mixed $value): string
  {
    $placeholder = $this->setParam($field, $value);
    return "$field = $placeholder";
  }
}
