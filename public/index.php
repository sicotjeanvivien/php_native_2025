<?php

/**
 * Define the root path of the application.
 */

use AWSD\Utils\Log;

define("ROOT_PATH", dirname(__DIR__));

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
  Log::captureError($e);
  http_response_code(500);
  echo "Critical Error: Unable to load classes.";
  exit;
}

use AWSD\App;
use AWSD\Exception\HttpException;
use AWSD\Template\View;

/**
 * Bootstrap the application and handle exceptions.
 */
try {
  App::bootstrap();
} catch (HttpException $e) {
  View::renderError($e->getStatusCode(), $e->getMessage());
  Log::captureError($e);
  exit;
} catch (\Throwable $e) {
  View::renderError(500, "An unexpected error occurred.");
  Log::captureError($e);
  header("Content-Type: text/plain; charset=utf-8");
  exit;
}