<?php

declare(strict_types=1);

namespace AWSD\Schema\Query\Register;

use AWSD\Schema\Query\Definition\FieldDefinition;

/**
 * Register for tracking fields and their aliases for a given table.
 *
 * This class handles normalized tracking of SQL fields for a single table context,
 * including alias generation and conflict prevention.
 */
final class FieldsRegister extends AbstractRegister
{

  /** @var FieldDefinition[] List of registered fields */
  private array $fields = [];

  /**
   * @param string $table Table name associated with this register (default context)
   */
  public function __construct(string $table)
  {
    parent::__construct($table);
  }

  /**
   * Register a field or a list of fields with optional aliasing.
   *
   * Supported formats:
   *   - 'id'
   *   - ['field' => 'alias']
   *   - ['other_table' => 'field']
   *   - ['other_table' => ['field' => 'alias']]
   *
   * @param string|array $field
   *
   * @throws \InvalidArgumentException|\LogicException
   */
  public function register(string|array $field): void
  {
    match (true) {
      is_string($field) => $this->addFieldDefinition($this->table, $field, null),
      is_array($field)  => $this->processArrayField($field),
      default           => throw new \InvalidArgumentException("Unsupported field format.")
    };
  }

  /**
   * Get all registered fields.
   *
   * @return FieldDefinition[]
   */
  public function getAll(): array
  {
    return $this->fields;
  }

  /**
   * Reset the registry (remove all fields).
   */
  public function reset(): void
  {
    $this->fields = [];
  }

  /**
   * Handle array-based field definitions and normalize them.
   *
   * @param array $field
   *
   * @throws \InvalidArgumentException|\LogicException
   */
  private function processArrayField(array $field): void
  {
    foreach ($field as $key => $value) {
      if (is_string($key) && is_array($value)) {
        $this->processNestedField($key, $value);
        continue;
      }

      [$table, $column, $alias] = $this->normalizeFieldEntry($key, $value);
      $this->addFieldDefinition($table, $column, $alias);
    }
  }

  /**
   * Handle nested array fields: ['table' => ['field' => 'alias']]
   *
   * @param string $table
   * @param array<string, string> $subfields
   *
   * @throws \InvalidArgumentException
   */
  private function processNestedField(string $table, array $subfields): void
  {
    foreach ($subfields as $subField => $alias) {
      match (true) {
        is_string($subField) && is_string($alias) => $this->addFieldDefinition($table, $subField, $alias),
        default                                   => throw new \InvalidArgumentException("Invalid nested field format in register()")
      };
    }
  }

  /**
   * Normalize one field entry into a (table, column, alias) triplet.
   *
   * @param mixed $key
   * @param mixed $value
   *
   * @return array{string, string, string|null}
   *
   * @throws \InvalidArgumentException
   */
  private function normalizeFieldEntry(mixed $key, mixed $value): array
  {
    return match (true) {
      is_int($key) && is_string($value)     => [$this->table, $value, null],
      is_string($key) && is_string($value)  =>[$this->table, $key, $value],
      default                               => throw new \InvalidArgumentException("Invalid field array format in register()")
    };
  }

  /**
   * Add a field definition if not already registered.
   *
   * @param string $table
   * @param string $field
   * @param string|null $alias
   *
   * @throws \LogicException
   */
  private function addFieldDefinition(string $table, string $field, ?string $alias): void
  {
    if (empty($alias)) $alias = $this->generateAlias($table, $field);
    if ($this->isFieldAlreadyRegistered($table, $field, $alias)) return;

    $this->fields[] = new FieldDefinition($table, $field, $alias);
  }

  /**
   * Check if a field has already been registered with a matching alias.
   *
   * @param string $table
   * @param string $column
   * @param string $alias
   *
   * @return bool
   *
   * @throws \LogicException If the field is registered with a different alias
   */
  private function isFieldAlreadyRegistered(string $table, string $column, string $alias): bool
  {
    foreach ($this->fields as $field) {
      if ($field->table === $table && $field->column === $column) {
        if ($field->alias !== $alias) {
          throw new \LogicException("Field '{$table}.{$column}' already registered with alias '{$field->alias}'");
        }
        return true;
      }
    }
    return false;
  }

}
