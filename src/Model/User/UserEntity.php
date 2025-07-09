<?php

namespace App\Model\User;

use AWSD\Model\AbstractEntity;
use AWSD\Database\Schema\Attribute\Type;
use AWSD\Database\Schema\Enum\EntityType;

class UserEntity extends AbstractEntity
{
  #[Type(type: EntityType::INT, primary: true, autoincrement: true)]
  protected int $id;

  #[Type(type: EntityType::STRING, nullable: false)]
  protected string $email;

  #[Type(type: EntityType::STRING, nullable: false)]
  protected string $password;

  public function getId(): int
  {
    return $this->id;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getPassword(): string
  {
    return $this->password;
  }
}
