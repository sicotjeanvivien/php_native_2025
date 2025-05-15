<?php

define("ROOT_PATH", dirname(__DIR__));
define("EXT_FILE", ".php");

use AWSD\Autoloader;

require_once __DIR__ . '/../lib/Autoloader.php';

$autoloader = new Autoloader([
  'App' => 'src',
  'AWSD' => 'lib',
]);

$autoloader->register();
