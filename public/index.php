<?php

use App\Service\Router;

define("ROOT_PATH", dirname(__DIR__));
define("EXT_FILE", ".php");

require_once ROOT_PATH . "/lib/Autoloader.php";

Use AWSD\Autoloader;

$autoloader = new Autoloader([
    "App" => "src",
    "AWSD" => "lib",
]);
$autoloader->register();

new Router();

require_once ROOT_PATH . "/src/template/base.phtml";