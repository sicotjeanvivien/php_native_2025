#!/usr/bin/env php
<?php


require_once __DIR__ . '/../lib/Script/AbstractScript.php';
require_once __DIR__ . '/../lib/Script/MigrationScript.php';

use App\Script\MigrationScript;

(new MigrationScript())->execute();
