<?php

namespace AWSD\SqlEntity\Mapper\Sgbd;

interface SgbdMapperInterface
{

  public function getType(): string;

  public function getConstraints(): string;
  
}
