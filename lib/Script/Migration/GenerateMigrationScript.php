<?php

namespace AWSD\Script\Migration;

use AWSD\Database\MigrationManager;
use AWSD\Script\AbstractScript;

class GenerateMigrationScript extends AbstractScript
{
  protected function run(): void
  {
    $manager = new MigrationManager();
    $manager->generate();
  }
}
