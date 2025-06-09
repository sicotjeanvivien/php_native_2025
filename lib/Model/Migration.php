<?php

namespace AWSD\Model;

use AWSD\SqlEntity\Attribute\Type;
use AWSD\SqlEntity\Enum\TypeEnum;
use DateTime;

class Migration
{

  #[Type(type: TypeEnum::INT, primary: true, autoincrement: true)]
  protected int $id;

  #[Type(type: TypeEnum::TEXT, nullable: false)]
  private string $filename;

  #[Type(type: TypeEnum::DATETIME, nullable: false, default: 'CURRENT_TIMESTAMP')]
  private DateTime $executedAt;

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
    return $this->executedAt;
  }
}
