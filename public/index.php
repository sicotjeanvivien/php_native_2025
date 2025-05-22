<?php

/**
 * Define the root path of the application.
 */
define("ROOT_PATH", dirname(__DIR__));

/**
 * Define the file extension for PHP files.
 */
define("EXT_FILE", ".php");

/**
 * Include the Autoloader.
 */
require_once ROOT_PATH . "/lib/Autoloader.php";

/**
 * Initialize the autoloader with aliases and register it.
 */
try {
  $aliases = ["App" => "src", "AWSD" => "lib"];
  $autoloader = new \AWSD\Autoloader($aliases, ROOT_PATH, EXT_FILE);
  $autoloader->register();
} catch (\Throwable $e) {
  error_log(sprintf(
    "[%s] Autoloader error: %s in %s:%d",
    date("Y-m-d H:i:s"),
    $e->getMessage(),
    $e->getFile(),
    $e->getLine()
  ));
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
  error_log(sprintf("[%s] %s in %s:%d", date("Y-m-d H:i:s"), $e->getMessage(), $e->getFile(), $e->getLine()));
  exit;
} catch (\Throwable $e) {
  View::renderError(500, "An unexpected error occurred.");
  error_log(sprintf("[%s] %s in %s:%d", date("Y-m-d H:i:s"), $e->getMessage(), $e->getFile(), $e->getLine()));
  header("Content-Type: text/plain; charset=utf-8");
  exit;
}
