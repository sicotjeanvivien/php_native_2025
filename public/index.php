<?php

/**
 * Define the root path of the application.
 */
if (!defined('ROOT_PATH')) {
  define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/lib/init.php';

use AWSD\Utils\Log;
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
