<?php

namespace App\Model\User;

use AWSD\Model\AbstractEntity;

class UserEntity extends AbstractEntity
{
  protected int $id;
  protected string $email;
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
