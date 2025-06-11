<?php

namespace AWSD\Schema\Mapper\SGBD;

use AWSD\Schema\Attribute\Type;
use AWSD\Schema\Enum\EntityType;

abstract class AbstractTypeMapper extends AbstractSGBDMapper
{

  /**
   * The metadata extracted from the #[Type] attribute of the entity property.
   *
   * @var Type|null
   */
  protected readonly ?Type $metadata;

  /**
   * The logical type of the entity property, resolved as an EntityType.
   *
   * @var EntityType
   */
  protected readonly EntityType $typeSql;

  /**
   * @param Type|null    $metadata The #[Type] metadata for the property.
   * @param EntityType   $typeSql  The resolved entity type (based on PHP type or attribute).
   */
  public function __construct(?Type $metadata, EntityType $typeSql)
  {
    $this->metadata = $metadata;
    $this->typeSql = $typeSql;
  }
}
