<?php

declare(strict_types=1);

namespace AWSD\Script\Database;

use AWSD\Database\Manager\Reset\ResetManager;
use AWSD\Script\AbstractScript;

final class ResetScript extends AbstractScript
{
  protected function run(): void
  {
    $resetManager = new ResetManager();
  }
}
