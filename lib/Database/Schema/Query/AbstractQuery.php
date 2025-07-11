<?php

namespace AWSD\Database\Schema\Query;

use AWSD\Database\Schema\Helper\StringHelper;
use AWSD\Database\Schema\Mapper\Orchestrator\QuoteOrchestrator;
use ReflectionClass;
use ReflectionProperty;

/**
 * AbstractQuery
 *
 * Base class for all SQL query generators (e.g., CreateQuery, InsertQuery).
 * Provides utilities to extract metadata from an entity object using reflection,
 * resolve the table name, and build column definitions for SQL generation.
 */
abstract class AbstractQuery
{
  /**
   * The entity class-string to generate a query for.
   *
   * @var class-string
   */
  protected string $entity;

  /**
   * ReflectionClass used to analyze the entity's structure.
   *
   * @var ReflectionClass
   */
  protected ReflectionClass $reflection;

  /**
   * Resolved table name for the entity.
   *
   * @var string
   */
  protected string $tableName;

  /**
   * Resolved attributes for the entity
   * 
   * @var array
   */
  protected array $metadata;

  /**
   * 
   */
  protected QuoteOrchestrator $quote;

  /**
   * @param class-string $entity          The FQCN of the entity (e.g. Post::class)
   * @param array<string, mixed> $entityAttributes Optional entity metadata (table name, fields, etc.)
   */
  public function __construct(string $entity, array $entityAttributes)
  {
    $this->entity = $entity;
    $this->reflection = new ReflectionClass($this->entity);
    $this->tableName = $this->resolveTableName();
    $this->metadata =  $this->getMetadata($entityAttributes);
    $this->quote = new QuoteOrchestrator();
  }

  /**
   * Infers the table name from the entity class.
   * Default implementation converts the class short name to lowercase and appends 's'.
   *
   * @return string
   */
  protected function resolveTableName(): string
  {
    $name = $this->reflection->getShortName();
    $name = str_replace('Entity', '', $name);

    if (str_ends_with($name, 'y')) {
      return strtolower(substr($name, 0, -1)) . 'ies';
    }

    return StringHelper::camelToSnake(strtolower($name) . 's');
  }

  /**
   * Returns all declared properties of the entity as ReflectionProperty instances.
   *
   * @return ReflectionProperty[]
   */
  protected function getEntityProperties(): array
  {
    return $this->reflection->getProperties();
  }

  protected function getMetadata(array $entityAttributes): array
  {
    $metadata = [];
    foreach ($this->getEntityProperties() as $prop) {
      foreach ($entityAttributes as $entityAttr) {
        $attrValues = $prop->getAttributes($entityAttr);
        if (!empty($attrValues)) {
          $metadata[$entityAttr][$prop->getName()] = $attrValues[0]->newInstance();
        }
      }
    }
    return $metadata;
  }
}
