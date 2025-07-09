<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\Component;

use AWSD\Database\Schema\Mapper\Orchestrator\QuoteOrchestrator;

/**
 * Class AbstractQueryComponent
 *
 * Base class for SQL query components handling parameter generation and binding.
 * Centralizes common logic related to placeholders and PDO parameter registration.
 *
 * @package AWSD\Database\Schema\Query\Component
 */
abstract class AbstractQueryComponent implements QueryComponentInterface
{
  /**
   * @var array<string, mixed> Parameters to be bound in the final query.
   */
  protected array $params = [];

  protected QuoteOrchestrator $quote;

  public function __construct()
  {
    $this->quote = new QuoteOrchestrator();
  }

  /**
   * Returns all registered parameters to be bound.
   *
   * @return array<string, mixed> List of parameter names and their values.
   */
  public function getParams(): array
  {
    return $this->params;
  }

  /**
   * Registers a parameter under a specific placeholder name.
   *
   * @param string $placeholder A string starting with ':'.
   * @param mixed $value The value to bind to the placeholder.
   */
  protected function registerParam(string $placeholder, mixed $value): void
  {
    $this->params[$placeholder] = $value;
  }

  /**
   * Generates a unique placeholder name for a given field.
   *
   * @param string $field The base field name.
   * @param int $suffix An optional suffix to avoid name collisions.
   * @return string A parameter placeholder (e.g., ":email_2").
   */
  protected function generatePlaceholder(string $field, int $suffix = 1): string
  {
    $placeholder = ':' . $field . ($suffix ? "_$suffix" : '');
    if (array_key_exists($placeholder, $this->params)) {
      return $this->generatePlaceholder($field, ++$suffix);
    }
    return $placeholder;
  }
}
