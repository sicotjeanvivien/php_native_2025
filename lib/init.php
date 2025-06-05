<?php

/**
 * Define the file extension for PHP files.
 */
define("EXT_FILE", ".php");

/**
 * Include the Autoloader.
 */
require_once ROOT_PATH . "/lib/Utils/Autoloader.php";

/**
 * Initialize the autoloader with aliases and register it.
 */
try {
  $aliases = ["App" => "src", "AWSD" => "lib"];
  $autoloader = new \AWSD\Utils\Autoloader($aliases, ROOT_PATH, EXT_FILE);
  $autoloader->register();
} catch (\Throwable $e) {
  http_response_code(500);
  echo $e->getMessage();
  echo "Critical Error: Unable to load classes.";
  exit;
}
