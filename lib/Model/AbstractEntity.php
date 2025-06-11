<?php

namespace AWSD\Model;

use AWSD\Schema\Attribute\Trigger;
use DateTime;
use AWSD\Schema\Attribute\Type;
use AWSD\Schema\Enum\EntityType;

/**
 * AbstractEntity
 *
 * Base class for all entity models.
 * Provides common fields such as ID, creation date, and update date.
 * These fields are pre-annotated with #[Type] to define their SQL mapping behavior.
 *
 * Any concrete entity should extend this class to inherit ID and timestamp support.
 */
abstract class AbstractEntity
{
  /**
   * Primary key for the entity.
   * Auto-incremented integer, mapped to SERIAL / AUTO_INCREMENT / AUTOINCREMENT depending on the SQL dialect.
   */
  #[Type(type: EntityType::INT, primary: true, autoincrement: true)]
  protected int $id;

  /**
   * Creation timestamp.
   * Defaulted to CURRENT_TIMESTAMP by the database engine.
   */
  #[Type(type: EntityType::DATETIME, default: "CURRENT_TIMESTAMP")]
  protected DateTime $createdAt;

  /**
   * Last update timestamp.
   * Defaulted to CURRENT_TIMESTAMP; may be updated by database logic or manually.
   */
  #[Type(type: EntityType::DATETIME, default: "CURRENT_TIMESTAMP")]
  #[Trigger(onUpdate: true)]
  protected DateTime $updatedAt;
}
