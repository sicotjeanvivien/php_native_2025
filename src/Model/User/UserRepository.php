<?php

namespace App\Model\User;

use AWSD\Model\AbstractRepository;

final class UserRepository extends AbstractRepository
{
  public function __construct()
  {
    parent::__construct();
    $this->table = 'users';
    $this->entityClass = UserEntity::class;
    
  }
}
