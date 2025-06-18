<?php

define("ROOT_PATH", dirname(__DIR__));
define("EXT_FILE", ".php");
const ROUTES = [];

use AWSD\Utils\Autoloader;

require_once __DIR__ . '/../lib/Utils/Autoloader.php';

$aliases = ["App" => "src", "AWSD" => "lib"];
$autoloader = new Autoloader($aliases, ROOT_PATH, EXT_FILE);
$autoloader->register();
