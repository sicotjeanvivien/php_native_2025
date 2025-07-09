<?php

declare(strict_types=1);

namespace AWSD\Database\Schema\Query\Register;

interface RegisterInterface
{
  public function register(string|array $element): void;

  public function getAll(): array;
}
