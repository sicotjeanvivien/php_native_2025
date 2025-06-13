<?php

namespace AWSD\Schema\Migration;

use AWSD\Schema\Attribute\Type;
use AWSD\Schema\Enum\EntityType;
use DateTime;

class Migration
{

  #[Type(type: EntityType::INT, primary: true, autoincrement: true)]
  protected int $id;

  #[Type(type: EntityType::TEXT, nullable: false)]
  private string $filename;

  #[Type(type: EntityType::DATETIME, nullable: false, default: 'CURRENT_TIMESTAMP')]
  private DateTime $executed_at;

  public function getId(): int
  {
    return $this->id;
  }

  public function getFileName(): string
  {
    return $this->filename;
  }

  public function getExecutedAt(): DateTime
  {
    return $this->executed_at;
  }
}
