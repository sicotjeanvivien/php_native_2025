<?php

namespace AWSD\Model;

use AWSD\SqlEntity\Attribute\Type;
use AWSD\SqlEntity\Enum\TypeEnum;
use DateTime;

abstract class AbstractEntity
{
  #[Type(type: TypeEnum::INT, primary: true, autoincrement: true)]
  protected int $id;

  #[Type(type: TypeEnum::DATETIME, default: "CURRENT_TIMESTAMP")]
  protected DateTime $createdAt;

  #[Type(type: TypeEnum::DATETIME, default: "CURRENT_TIMESTAMP")]
  protected DateTime $updatedAt;
}
